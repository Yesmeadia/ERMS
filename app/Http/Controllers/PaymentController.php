<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassMaster;
use App\Models\School;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

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
            ->with(['students.class'])
            ->withCount('students')
            ->latest()
            ->take(3)
            ->get();

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
     * School Admin: Display paginated Payment Transaction History activity list.
     */
    public function transactions(Request $request)
    {
        $school = Auth::user()->school;

        $payments = Payment::where('school_id', $school->id)
            ->with(['students.class'])
            ->withCount('students')
            ->latest()
            ->paginate(15);

        return view('school-admin.payments.transactions', compact('payments'));
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
            $fee = $student->registration_fee;

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
     * School Admin: Create a Razorpay order and redirect back to checkout with order details.
     */
    public function initiate(Request $request)
    {
        $studentIds = $request->input('student_ids');

        if (empty($studentIds)) {
            return redirect()->route('school.students.index')->with('error', 'Please select at least one student to pay registration fee.');
        }

        $school = Auth::user()->school;

        // Fetch unpaid students belonging to this school
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
            $totalAmount += $student->registration_fee;
        }

        // Create Razorpay order (amount in paise)
        $api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );

        $razorpayOrder = $api->order->create([
            'receipt'         => 'ERMS_' . strtoupper(bin2hex(random_bytes(4))),
            'amount'          => (int) round($totalAmount * 100), // paise
            'currency'        => 'INR',
            'payment_capture' => 1, // auto-capture
        ]);

        // Create a Pending payment record
        $payment = Payment::create([
            'school_id'         => $school->id,
            'razorpay_order_id' => $razorpayOrder->id,
            'amount'            => $totalAmount,
            'payment_method'    => 'Razorpay',
            'status'            => 'Pending',
            'paid_at'           => null,
        ]);

        // Attach students to this payment record
        foreach ($students as $student) {
            $payment->students()->attach($student->id, ['amount' => $student->registration_fee]);
        }

        // Pass checkout details to the checkout view via session
        session([
            'razorpay_order_id'    => $razorpayOrder->id,
            'razorpay_payment_id'  => $payment->id,  // internal DB id
            'razorpay_amount'      => (int) round($totalAmount * 100),
            'razorpay_student_ids' => $students->pluck('id')->toArray(),
        ]);

        // Reload the checkout view with the order now ready
        $classBreakdown = [];
        foreach ($students as $student) {
            $classId   = $student->class_id;
            $className = $student->class->name;
            $fee       = $student->registration_fee;

            if (!isset($classBreakdown[$classId])) {
                $classBreakdown[$classId] = ['name' => $className, 'count' => 0, 'fee' => $fee, 'total' => 0.00];
            }
            $classBreakdown[$classId]['count']++;
            $classBreakdown[$classId]['total'] += $fee;
        }

        return view('school-admin.payments.checkout', compact('students', 'totalAmount', 'classBreakdown'))
            ->with([
                'razorpayOrderId' => $razorpayOrder->id,
                'razorpayAmount'  => (int) round($totalAmount * 100),
                'razorpayKeyId'   => config('services.razorpay.key_id'),
                'paymentDbId'     => $payment->id,
                'schoolName'      => $school->name,
                'adminEmail'      => Auth::user()->email,
                'adminName'       => Auth::user()->name,
            ]);
    }

    /**
     * School Admin: Handle Razorpay payment callback.
     * Verifies HMAC-SHA256 signature before marking payment as complete.
     */
    public function callback(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id'   => ['required', 'string'],
            'razorpay_signature'  => ['required', 'string'],
            'payment_db_id'       => ['required', 'exists:payments,id'],
        ]);

        $payment = Payment::findOrFail($request->payment_db_id);
        $school  = Auth::user()->school;

        if ($payment->school_id !== $school->id || $payment->status !== 'Pending') {
            abort(403, 'Invalid payment session.');
        }

        // Verify HMAC-SHA256 signature
        $api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);
        } catch (SignatureVerificationError $e) {
            // Signature mismatch — mark as Failed
            DB::transaction(function () use ($payment) {
                $payment->status = 'Failed';
                $payment->save();

                activity()
                    ->performedOn($payment)
                    ->log('Razorpay: Signature verification failed. Payment marked as Failed.');
            });

            return redirect()->route('school.students.index')
                ->with('error', 'Payment verification failed. Please contact support if amount was debited.');
        }

        // Signature verified — mark payment as complete
        DB::transaction(function () use ($payment, $request) {
            $payment->status               = 'Paid';
            $payment->transaction_id       = $request->razorpay_payment_id;
            $payment->razorpay_payment_id  = $request->razorpay_payment_id;
            $payment->paid_at              = now();
            $payment->save();

            // Mark all attached students as paid and submitted
            foreach ($payment->students as $student) {
                $student->payment_status = 'Paid';
                $student->status         = 'Submitted';
                $student->save();

                activity()
                    ->performedOn($student)
                    ->log('Registration payment completed via Razorpay. Status updated to Submitted.');
            }

            activity()
                ->performedOn($payment)
                ->log('Razorpay: Payment ' . $request->razorpay_payment_id . ' verified. ₹' . number_format($payment->amount, 2) . ' collected.');
        });

        return redirect()->route('school.payments.receipt', $payment->id)
            ->with('success', 'Payment of ₹' . number_format($payment->amount, 2) . ' collected successfully! Candidates submitted to the board.');
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

        // Outstanding calculations (Draft and unpaid students across active classes/categories)
        $unpaidBreakdown = DB::table('students')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
            ->where('students.payment_status', 'Unpaid')
            ->select(DB::raw('SUM(CASE WHEN categories.registration_fee > 0 THEN categories.registration_fee ELSE classes.registration_fee END) as outstanding'))
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
