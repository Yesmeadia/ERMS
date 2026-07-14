<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassMaster;
use App\Models\School;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

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

        if (empty($studentIds) || !is_array($studentIds)) {
            return redirect()->route('school.students.index')->with('error', 'Please select at least one student to pay registration fee.');
        }

        $school = Auth::user()->school;

        // Check if any selected student is missing a photo
        $unphotographedCount = Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->where(function ($q) {
                $q->whereNull('photograph')->orWhere('photograph', '');
            })
            ->count();

        if ($unphotographedCount > 0) {
            return redirect()->route('school.students.index')->with('error', 'Student photo is not uploaded.');
        }

        // CWE-639: Validate ownership and payment status of every student ID to prevent parameter manipulation
        $validUnpaidStudentCount = Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->where('payment_status', 'Unpaid')
            ->count();

        if ($validUnpaidStudentCount !== count(array_unique($studentIds))) {
            return redirect()->route('school.students.index')->with('error', 'One or more selected students are invalid, already paid, or do not belong to your school.');
        }

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
     * School Admin: Create a Cashfree order and redirect to checkout with session details.
     */
    public function initiate(Request $request)
    {
        $studentIds = $request->input('student_ids');

        if (empty($studentIds) || !is_array($studentIds)) {
            return redirect()->route('school.students.index')->with('error', 'Please select at least one student to pay registration fee.');
        }

        $school = Auth::user()->school;

        // Check if any selected student is missing a photo
        $unphotographedCount = Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->where(function ($q) {
                $q->whereNull('photograph')->orWhere('photograph', '');
            })
            ->count();

        if ($unphotographedCount > 0) {
            return redirect()->route('school.students.index')->with('error', 'Student photo is not uploaded.');
        }

        // CWE-639: Validate ownership and payment status of every student ID to prevent parameter manipulation
        $validUnpaidStudentCount = Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->where('payment_status', 'Unpaid')
            ->count();

        if ($validUnpaidStudentCount !== count(array_unique($studentIds))) {
            return redirect()->route('school.students.index')->with('error', 'One or more selected students are invalid, already paid, or do not belong to your school.');
        }

        // Cashfree API configuration
        $isProduction = config('services.cashfree.env') === 'production';
        $baseUrl = $isProduction
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
        $clientId = config('services.cashfree.client_id');
        $clientSecret = config('services.cashfree.client_secret');

        $headers = [
            'x-client-id' => $clientId,
            'x-client-secret' => $clientSecret,
            'x-api-version' => '2023-08-01',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // CWE-362: Check for an exact matching pending payment to reuse and prevent duplicate charges/orders
        $matchingPayment = Payment::where('school_id', $school->id)
            ->where('status', 'Pending')
            ->whereHas('students', function ($query) use ($studentIds) {
                $query->whereIn('students.id', $studentIds);
            }, '=', count($studentIds))
            ->withCount('students')
            ->get()
            ->first(function ($p) use ($studentIds) {
                return $p->students_count === count($studentIds);
            });

        $paymentSessionId = null;
        $cfOrderId = null;
        $paymentDbId = null;
        $totalAmount = 0.00;
        $students = collect();

        if ($matchingPayment) {
            $cfOrderId = $matchingPayment->cashfree_order_id;
            $paymentDbId = $matchingPayment->id;

            // Verify existing session against Cashfree
            try {
                $http = new GuzzleClient(['timeout' => 15]);
                $cfResponse = $http->get($baseUrl . '/orders/' . $cfOrderId, [
                    'headers' => $headers,
                ]);
                $cfOrder = json_decode((string) $cfResponse->getBody(), true);
                if (isset($cfOrder['payment_session_id']) && ($cfOrder['order_status'] ?? '') === 'ACTIVE') {
                    $paymentSessionId = $cfOrder['payment_session_id'];
                    $totalAmount = $matchingPayment->amount;
                    $students = $matchingPayment->students;
                } else {
                    // Mark as failed if order is not active or payment_session_id is missing, let it fall through to create a new one
                    $matchingPayment->status = 'Failed';
                    $matchingPayment->save();
                    $matchingPayment = null;
                }
            } catch (\Exception $e) {
                $matchingPayment->status = 'Failed';
                $matchingPayment->save();
                $matchingPayment = null;
            }
        }

        // If no matching payment session was found or verified, initiate a new one
        if (!$matchingPayment) {
            try {
                // CWE-362 & CWE-602: Perform database locks and calculations inside a transaction
                $checkoutData = DB::transaction(function () use ($school, $studentIds, $baseUrl, $headers) {
                    // Lock student rows for update to ensure serial execution under high concurrency
                    $students = Student::where('school_id', $school->id)
                        ->whereIn('id', $studentIds)
                        ->where('payment_status', 'Unpaid')
                        ->lockForUpdate()
                        ->get();

                    if ($students->isEmpty() || $students->count() !== count(array_unique($studentIds))) {
                        throw new \Exception('One or more selected students are invalid, already paid, or do not belong to your school.');
                    }

                    // Cancel/Fail any older Pending payments that contain overlapping student IDs
                    $overlappingPaymentIds = DB::table('payment_student')
                        ->join('payments', 'payment_student.payment_id', '=', 'payments.id')
                        ->where('payments.school_id', $school->id)
                        ->where('payments.status', 'Pending')
                        ->whereIn('payment_student.student_id', $studentIds)
                        ->pluck('payments.id')
                        ->unique();

                    if ($overlappingPaymentIds->isNotEmpty()) {
                        Payment::whereIn('id', $overlappingPaymentIds)
                            ->update(['status' => 'Failed']);
                    }

                    // CWE-602: Always calculate total payable amount server-side based on secure DB records
                    $totalAmount = 0.00;
                    foreach ($students as $student) {
                        $totalAmount += $student->registration_fee;
                    }

                    // Build a unique order ID
                    $cfOrderId = 'ERMS_' . strtoupper(bin2hex(random_bytes(8)));
                    $returnUrl = route('school.payments.callback') . '?order_id=' . $cfOrderId;

                    $payload = [
                        'order_id' => $cfOrderId,
                        'order_amount' => round($totalAmount, 2),
                        'order_currency' => 'INR',
                        'order_note' => 'YES GENIUS — Registration Fee for ' . $school->name,
                        'customer_details' => [
                            'customer_id' => 'school_' . $school->id,
                            'customer_phone' => '9999999999',
                            'customer_email' => Auth::user()->email,
                            'customer_name' => Auth::user()->name,
                        ],
                        'order_meta' => [
                            'return_url' => $returnUrl,
                        ],
                    ];

                    $http = new GuzzleClient(['timeout' => 15]);
                    $cfResponse = $http->post($baseUrl . '/orders', [
                        'headers' => $headers,
                        'json' => $payload,
                    ]);
                    $cfOrder = json_decode((string) $cfResponse->getBody(), true);
                    $paymentSessionId = $cfOrder['payment_session_id'] ?? null;

                    if (!$paymentSessionId) {
                        throw new \Exception('Failed to get payment session ID from Cashfree.');
                    }

                    // Create the payment record in DB
                    $payment = Payment::create([
                        'school_id' => $school->id,
                        'cashfree_order_id' => $cfOrderId,
                        'amount' => $totalAmount,
                        'payment_method' => 'Cashfree',
                        'status' => 'Pending',
                        'paid_at' => null,
                    ]);

                    // Attach students to this payment record
                    foreach ($students as $student) {
                        $payment->students()->attach($student->id, ['amount' => $student->registration_fee]);
                    }

                    return [
                        'paymentDbId' => $payment->id,
                        'cfOrderId' => $cfOrderId,
                        'paymentSessionId' => $paymentSessionId,
                        'totalAmount' => $totalAmount,
                        'students' => $students,
                    ];
                });

                $paymentDbId = $checkoutData['paymentDbId'];
                $cfOrderId = $checkoutData['cfOrderId'];
                $paymentSessionId = $checkoutData['paymentSessionId'];
                $totalAmount = $checkoutData['totalAmount'];
                $students = $checkoutData['students'];

            } catch (\Exception $e) {
                return redirect()->route('school.students.index')
                    ->with('error', 'Could not initiate payment: ' . $e->getMessage());
            }
        }

        // Reload the checkout view with Cashfree session details
        $classBreakdown = [];
        foreach ($students as $student) {
            $classId = $student->class_id;
            $className = $student->class->name;
            $fee = $student->registration_fee;

            if (!isset($classBreakdown[$classId])) {
                $classBreakdown[$classId] = ['name' => $className, 'count' => 0, 'fee' => $fee, 'total' => 0.00];
            }
            $classBreakdown[$classId]['count']++;
            $classBreakdown[$classId]['total'] += $fee;
        }

        return view('school-admin.payments.checkout', compact('students', 'totalAmount', 'classBreakdown'))
            ->with([
                'cashfreeOrderId' => $cfOrderId,
                'paymentSessionId' => $paymentSessionId,
                'cashfreeEnv' => config('services.cashfree.env', 'sandbox'),
                'paymentDbId' => $paymentDbId,
                'schoolName' => $school->name,
                'adminEmail' => Auth::user()->email,
                'adminName' => Auth::user()->name,
            ]);
    }

    /**
     * School Admin: Handle Cashfree payment return (GET redirect from Cashfree hosted page).
     * Verifies payment by fetching order status from Cashfree API.
     */
    public function callback(Request $request)
    {
        $cfOrderId = $request->query('order_id');

        if (!$cfOrderId) {
            return redirect()->route('school.students.index')
                ->with('error', 'Invalid payment return. Please contact support.');
        }

        $school = Auth::user()->school;

        try {
            // CWE-362: Lock payment row during verification to prevent race conditions with webhooks
            $payment = DB::transaction(function () use ($cfOrderId, $school) {
                return Payment::where('cashfree_order_id', $cfOrderId)
                    ->where('school_id', $school->id)
                    ->lockForUpdate()
                    ->first();
            });

            if (!$payment) {
                return redirect()->route('school.students.index')
                    ->with('error', 'Invalid payment session.');
            }

            // If already processed via Webhook/refresh, redirect to receipt
            if ($payment->status === 'Paid') {
                return redirect()->route('school.payments.receipt', $payment->id);
            }
        } catch (\Exception $e) {
            return redirect()->route('school.students.index')
                ->with('error', 'Database lock timeout or error during callback processing. Please try again.');
        }

        // Cashfree API base URL
        $isProduction = config('services.cashfree.env') === 'production';
        $baseUrl = $isProduction
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
        $clientId = config('services.cashfree.client_id');
        $clientSecret = config('services.cashfree.client_secret');

        $headers = [
            'x-client-id' => $clientId,
            'x-client-secret' => $clientSecret,
            'x-api-version' => '2023-08-01',
            'Accept' => 'application/json',
        ];

        $http = new GuzzleClient(['timeout' => 15]);

        try {
            $cfResponse = $http->get($baseUrl . '/orders/' . $cfOrderId, ['headers' => $headers]);
            $cfOrder = json_decode((string) $cfResponse->getBody(), true);
            $orderStatus = $cfOrder['order_status'] ?? 'UNKNOWN';
        } catch (RequestException $e) {
            // API error — fail gracefully without crashing
            return redirect()->route('school.students.index')
                ->with('error', 'Could not verify payment status. Please contact support if amount was debited.');
        }

        if (strtoupper($orderStatus) !== 'PAID') {
            // Payment was not completed
            DB::transaction(function () use ($payment, $orderStatus) {
                $payment->status = 'Failed';
                $payment->save();

                activity()
                    ->performedOn($payment)
                    ->log('Cashfree: Payment not completed. Order status: ' . $orderStatus . '. Payment marked as Failed.');
            });

            return redirect()->route('school.students.index')
                ->with('error', 'Payment was not completed (status: ' . $orderStatus . '). No amount was charged.');
        }

        // Payment successful — fetch the CF payment ID and actual payment method
        $cfPaymentId = null;
        $resolvedMethod = 'Cashfree'; // fallback
        try {
            $paymentsResponse = $http->get($baseUrl . '/orders/' . $cfOrderId . '/payments', ['headers' => $headers]);
            $cfPayments = json_decode((string) $paymentsResponse->getBody(), true);
            if (!empty($cfPayments) && isset($cfPayments[0]['cf_payment_id'])) {
                $cfPaymentId = (string) $cfPayments[0]['cf_payment_id'];
                $resolvedMethod = self::resolveCashfreePaymentMethod($cfPayments[0]);
            }
        } catch (RequestException $e) {
            // Non-critical — fall back to order ID if payment ID call fails
        }

        // Mark payment as complete
        DB::transaction(function () use ($payment, $cfOrderId, $cfPaymentId, $resolvedMethod) {
            $payment->status = 'Paid';
            $payment->transaction_id = $cfPaymentId ?? $cfOrderId;
            $payment->cashfree_payment_id = $cfPaymentId;
            $payment->payment_method = $resolvedMethod;
            $payment->paid_at = now();
            $payment->save();

            // Mark all attached students as paid and submitted
            foreach ($payment->students as $student) {
                $student->payment_status = 'Paid';
                $student->status = 'Submitted';
                $student->save();

                activity()
                    ->performedOn($student)
                    ->log('Registration payment completed via Cashfree. Status updated to Submitted.');
            }

            activity()
                ->performedOn($payment)
                ->log('Cashfree: Order ' . $cfOrderId . ' verified PAID. ₹' . number_format($payment->amount, 2) . ' collected.');
        });

        return redirect()->route('school.payments.receipt', $payment->id)
            ->with('success', 'Payment of ₹' . number_format($payment->amount, 2) . ' collected successfully! Candidates submitted to the board.');
    }

    /**
     * Cashfree Webhook: Handle async payment notifications.
     * CWE-345: Verifies HMAC-SHA256 signature before processing.
     * This route is exempt from CSRF (see bootstrap/app.php).
     */
    public function webhook(Request $request)
    {
        $webhookSecret = config('services.cashfree.webhook_secret');

        if ($webhookSecret) {
            // Verify signature: Cashfree sends x-webhook-signature and x-webhook-timestamp
            $timestamp = $request->header('x-webhook-timestamp');
            $signature = $request->header('x-webhook-signature');
            $rawBody = $request->getContent();

            if (!$timestamp || !$signature) {
                return response()->json(['status' => 'error', 'message' => 'Missing webhook signature headers'], 401);
            }

            $signedPayload = $timestamp . $rawBody;
            $expectedSig = base64_encode(hash_hmac('sha256', $signedPayload, $webhookSecret, true));

            if (!hash_equals($expectedSig, $signature)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid webhook signature'], 401);
            }
        }

        $payload = $request->json()->all();
        $eventType = $payload['type'] ?? null;
        $data = $payload['data'] ?? [];
        $orderData = $data['order'] ?? [];
        $cfOrderId = $orderData['order_id'] ?? null;

        // Only process PAYMENT_SUCCESS events
        if ($eventType !== 'PAYMENT_SUCCESS' || !$cfOrderId) {
            return response()->json(['status' => 'ok', 'message' => 'Event ignored']);
        }

        $payment = Payment::where('cashfree_order_id', $cfOrderId)
            ->where('status', 'Pending')
            ->first();

        if (!$payment) {
            // Already processed or unknown order — respond 200 to stop retries
            return response()->json(['status' => 'ok', 'message' => 'Payment already processed or not found']);
        }

        $cfPaymentId = (string) ($data['payment']['cf_payment_id'] ?? $cfOrderId);
        $resolvedMethod = self::resolveCashfreePaymentMethod($data['payment'] ?? []);

        DB::transaction(function () use ($payment, $cfOrderId, $cfPaymentId, $resolvedMethod) {
            // Re-check inside transaction with row lock to prevent race with callback
            $payment = Payment::where('cashfree_order_id', $cfOrderId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->status === 'Paid') {
                return; // Already handled by callback
            }

            $payment->status = 'Paid';
            $payment->transaction_id = $cfPaymentId;
            $payment->cashfree_payment_id = $cfPaymentId;
            $payment->payment_method = $resolvedMethod;
            $payment->paid_at = now();
            $payment->save();

            foreach ($payment->students as $student) {
                $student->payment_status = 'Paid';
                $student->status = 'Submitted';
                $student->save();

                activity()
                    ->performedOn($student)
                    ->log('Registration payment confirmed via Cashfree webhook. Status updated to Submitted.');
            }

            activity()
                ->performedOn($payment)
                ->log('Cashfree webhook: Order ' . $cfOrderId . ' confirmed PAID. ₹' . number_format($payment->amount, 2) . ' collected.');
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Resolve a human-readable payment method label from a Cashfree payment object.
     * Cashfree returns payment_group (e.g. "upi", "card", "net_banking") and
     * a nested payment_method object with instrument-specific details.
     */
    private static function resolveCashfreePaymentMethod(array $cfPayment): string
    {
        $group = strtolower($cfPayment['payment_group'] ?? '');
        $method = $cfPayment['payment_method'] ?? [];

        switch ($group) {
            case 'upi':
                $upiId = $method['upi']['upi_id'] ?? null;
                return $upiId ? 'UPI (' . $upiId . ')' : 'UPI';

            case 'card':
                $card = $method['card'] ?? [];
                $cardType = ucfirst(strtolower($card['card_type'] ?? '')); // DEBIT / CREDIT
                $network = ucfirst(strtolower($card['card_network'] ?? '')); // VISA / MASTERCARD
                $last4 = $card['card_number'] ?? null; // last 4 digits if available
                $label = trim($network . ' ' . $cardType . ' Card');
                if ($last4) {
                    $label .= ' (···' . substr($last4, -4) . ')';
                }
                return $label ?: 'Card';

            case 'net_banking':
            case 'netbanking':
                $bankName = $method['netbanking']['channel'] ?? $method['netbanking']['netbanking_bank_code'] ?? null;
                return $bankName ? 'Net Banking (' . $bankName . ')' : 'Net Banking';

            case 'wallet':
                $walletName = $method['app']['channel'] ?? null;
                return $walletName ? ucfirst($walletName) . ' Wallet' : 'Wallet';

            case 'emi':
                $emiCard = $method['emi']['card_network'] ?? null;
                return $emiCard ? 'EMI (' . ucfirst(strtolower($emiCard)) . ')' : 'EMI';

            case 'pay_later':
                return 'Pay Later';

            default:
                return $group ? ucwords(str_replace('_', ' ', $group)) : 'Cashfree';
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
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where('transaction_id', 'like', "%{$search}%");
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
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where('transaction_id', 'like', "%{$search}%");
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

            $sanitizeCsvField = function ($value) {
                if (is_null($value)) {
                    return '';
                }
                $value = (string) $value;
                if (strlen($value) > 0 && in_array(substr($value, 0, 1), ['=', '+', '-', '@'])) {
                    return "'" . $value;
                }
                return $value;
            };

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $sanitizeCsvField($payment->school->name),
                    $sanitizeCsvField($payment->school->code),
                    $sanitizeCsvField($payment->transaction_id),
                    $sanitizeCsvField($payment->payment_method),
                    $payment->students_count ?? $payment->students()->count(),
                    $payment->amount,
                    $sanitizeCsvField($payment->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
