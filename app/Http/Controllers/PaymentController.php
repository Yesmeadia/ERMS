<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassMaster;
use App\Models\School;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * School Admin: Display School Balance Sheet and Payment History.
     */
    public function index()
    {
        $school = Auth::user()->school;

        // --- 1. Balance Sheet computation ---
        // Get all active classes
        $classes = ClassMaster::where('status', true)->get();

        $balanceSheet = [];
        $totalRegistered = 0;
        $totalPaidCount = 0;
        $totalOutstandingCount = 0;
        $totalPaidAmount = 0.00;
        $totalOutstandingAmount = 0.00;

        foreach ($classes as $class) {
            // Count candidates in this school and this class
            $studentsQuery = Student::where('school_id', $school->id)
                ->where('class_id', $class->id);

            $totalCount = (clone $studentsQuery)->count();
            $paidCount = (clone $studentsQuery)->where('payment_status', 'Paid')->count();
            $unpaidCount = $totalCount - $paidCount;

            $fee = $class->registration_fee;
            $paidAmount = $paidCount * $fee;
            $unpaidAmount = $unpaidCount * $fee;

            if ($totalCount > 0) {
                $balanceSheet[] = [
                    'class_name' => $class->name,
                    'fee' => $fee,
                    'total_count' => $totalCount,
                    'paid_count' => $paidCount,
                    'unpaid_count' => $unpaidCount,
                    'paid_amount' => $paidAmount,
                    'unpaid_amount' => $unpaidAmount,
                ];

                $totalRegistered += $totalCount;
                $totalPaidCount += $paidCount;
                $totalOutstandingCount += $unpaidCount;
                $totalPaidAmount += $paidAmount;
                $totalOutstandingAmount += $unpaidAmount;
            }
        }

        // --- 2. Payment Transaction History ---
        $payments = Payment::where('school_id', $school->id)
            ->withCount('students')
            ->latest()
            ->paginate(10);

        return view('school-admin.payments.index', compact(
            'balanceSheet',
            'payments',
            'totalRegistered',
            'totalPaidCount',
            'totalOutstandingCount',
            'totalPaidAmount',
            'totalOutstandingAmount'
        ));
    }

    /**
     * School Admin: Display checkout page for selected students (individual or bulk).
     */
    public function checkout(Request $request)
    {
        $studentIds = $request->input('student_ids');

        if (empty($studentIds)) {
            return redirect()->route('school.students.index')->with('error', 'Please select at least one student to pay registration fee.');
        }

        $school = Auth::user()->school;

        // Fetch unpaid draft/rejected students
        $students = Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->where('payment_status', 'Unpaid')
            ->with('class')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('school.students.index')->with('error', 'No unpaid registrations found in your selection.');
        }

        // Calculate fees
        $totalAmount = 0.00;
        $classBreakdown = [];

        foreach ($students as $student) {
            $classId = $student->class_id;
            $className = $student->class->name;
            $fee = $student->class->registration_fee;

            $totalAmount += $fee;

            if (!isset($classBreakdown[$classId])) {
                $classBreakdown[$classId] = [
                    'name' => $className,
                    'count' => 0,
                    'fee' => $fee,
                    'total' => 0.00,
                ];
            }
            $classBreakdown[$classId]['count']++;
            $classBreakdown[$classId]['total'] += $fee;
        }

        return view('school-admin.payments.checkout', compact('students', 'totalAmount', 'classBreakdown'));
    }

    /**
     * School Admin: Initiate payment and redirect to Axis/Freecharge Sandbox simulator.
     */
    public function initiate(Request $request)
    {
        $studentIds = $request->input('student_ids');

        if (empty($studentIds)) {
            return redirect()->route('school.students.index')->with('error', 'Please select at least one student to pay registration fee.');
        }

        $school = Auth::user()->school;

        // Fetch unpaid draft/rejected students
        $students = Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->where('payment_status', 'Unpaid')
            ->with('class')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('school.students.index')->with('error', 'No valid unpaid registrations found.');
        }

        $totalAmount = 0.00;
        foreach ($students as $student) {
            $totalAmount += $student->class->registration_fee;
        }

        // Create a Pending payment record
        $payment = Payment::create([
            'school_id' => $school->id,
            'transaction_id' => 'TXN_' . strtoupper(bin2hex(random_bytes(6))),
            'amount' => $totalAmount,
            'payment_method' => 'Axis-Freecharge PG',
            'status' => 'Pending',
            'paid_at' => null,
        ]);

        // Attach students
        foreach ($students as $student) {
            $payment->students()->attach($student->id, ['amount' => $student->class->registration_fee]);
        }

        return redirect()->route('school.payments.gateway', $payment->id);
    }

    /**
     * School Admin: Display Mock Axis/Freecharge Sandbox Gateway.
     */
    public function gateway(Payment $payment)
    {
        $school = Auth::user()->school;

        if ($payment->school_id !== $school->id || $payment->status !== 'Pending') {
            return redirect()->route('school.payments.index')->with('error', 'Invalid payment session.');
        }

        $payment->load(['students.class', 'school']);

        return view('school-admin.payments.gateway', compact('payment'));
    }

    /**
     * School Admin: Process Axis/Freecharge Gateway callback status.
     */
    public function process(Request $request)
    {
        $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'status' => ['required', 'in:success,failed'],
        ]);

        $payment = Payment::findOrFail($request->payment_id);
        $school = Auth::user()->school;

        if ($payment->school_id !== $school->id) {
            abort(403);
        }

        if ($request->status === 'success') {
            DB::transaction(function () use ($payment) {
                $payment->status = 'Paid';
                $payment->paid_at = now();
                $payment->save();

                // Update all attached students
                foreach ($payment->students as $student) {
                    $student->payment_status = 'Paid';
                    $student->status = 'Submitted';
                    $student->save();

                    activity()
                        ->performedOn($student)
                        ->log("Registration payment completed via Axis-Freecharge Gateway. Status updated to Submitted.");
                }

                activity()
                    ->performedOn($payment)
                    ->log("Axis-Freecharge PG Sandbox: Received success callback for ₹" . number_format($payment->amount, 2));
            });

            return redirect()->route('school.payments.receipt', $payment->id)->with('success', 'Payment collections successful via Axis-Freecharge PG Sandbox! Candidates submitted to board.');
        } else {
            DB::transaction(function () use ($payment) {
                $payment->status = 'Failed';
                $payment->save();

                activity()
                    ->performedOn($payment)
                    ->log("Axis-Freecharge PG Sandbox: Received failed callback.");
            });

            return redirect()->route('school.students.index')->with('error', 'Payment transaction failed or cancelled on gateway.');
        }
    }

    /**
     * School Admin: Display Payment Receipt for a Transaction.
     */
    public function receipt(Payment $payment)
    {
        $school = Auth::user()->school;

        // Ensure this payment belongs to the logged-in school
        if ($payment->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        $payment->load(['students.class', 'school']);

        return view('school-admin.payments.receipt', compact('payment'));
    }

    /**
     * Super Admin: Global Payouts & Payments Report.
     */
    public function adminIndex(Request $request)
    {
        $query = Payment::with(['school', 'students']);

        // Filter by school
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search Transaction ID
        if ($request->filled('search')) {
            $query->where('transaction_id', 'like', "%{$request->search}%");
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        // Metrics
        $totalCollected = Payment::where('status', 'Paid')->sum('amount');

        // Outstanding calculations (Draft and unpaid students across active classes)
        $unpaidBreakdown = DB::table('students')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->where('students.payment_status', 'Unpaid')
            ->select(DB::raw('SUM(classes.registration_fee) as outstanding'))
            ->first();

        $totalOutstanding = $unpaidBreakdown->outstanding ?? 0.00;

        $paymentsCount = Payment::count();
        $activeSchoolsPaid = School::whereHas('payments')->count();

        $schools = School::where('status', true)->get();

        return view('super-admin.payments.index', compact(
            'payments',
            'schools',
            'totalCollected',
            'totalOutstanding',
            'paymentsCount',
            'activeSchoolsPaid'
        ));
    }

    /**
     * Super Admin: Export Payouts Report to CSV.
     */
    public function adminExport(Request $request)
    {
        $query = Payment::with(['school', 'students']);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('transaction_id', 'like', "%{$request->search}%");
        }

        $payments = $query->latest()->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=erms_payouts_report_" . time() . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Date', 'School Name', 'School Code', 'Transaction ID', 'Method', 'Candidates Count', 'Amount Paid (INR)', 'Status'];

        $callback = function () use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->school->name,
                    $payment->school->code,
                    $payment->transaction_id,
                    $payment->payment_method,
                    $payment->students_count ?? $payment->students()->count(),
                    $payment->amount,
                    $payment->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
