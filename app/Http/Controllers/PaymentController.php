<?php

namespace App\Http\Controllers;

use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $finePerStudent = $school->getFinePerStudentAmount();
        $isFineApplicable = $school->isFineApplicable();

        foreach ($classes as $class) {
            // Count candidates in this school and this class
            $studentsQuery = Student::where('school_id', $school->id)
                ->where('class_id', $class->id);

            $totalCount = (clone $studentsQuery)->count();
            $paidCount = (clone $studentsQuery)->where('payment_status', 'Paid')->count();
            $unpaidCount = $totalCount - $paidCount;

            $baseFee = $class->registration_fee;
            $fee = $baseFee + $finePerStudent;
            $paidAmount = $paidCount * $fee;
            $unpaidAmount = $unpaidCount * $fee;

            if ($totalCount > 0) {
                $balanceSheet[] = [
                    'class_name' => $class->name,
                    'fee' => $fee,
                    'base_fee' => $baseFee,
                    'fine_fee' => $finePerStudent,
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

        $isRegistrationClosed = Examination::isRegistrationClosed();

        return view('school-admin.payments.index', compact(
            'balanceSheet',
            'payments',
            'totalRegistered',
            'totalPaidCount',
            'totalOutstandingCount',
            'totalPaidAmount',
            'totalOutstandingAmount',
            'finePerStudent',
            'isFineApplicable',
            'isRegistrationClosed'
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
        if (Examination::isRegistrationClosed()) {
            return redirect()->route('school.students.index')->with('error', 'Registration is closed for this examination session. Payments for drafted candidates are no longer accepted.');
        }

        $studentIds = $request->input('student_ids');

        if (empty($studentIds) || ! is_array($studentIds)) {
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
            ->with(['class', 'category'])
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('school.students.index')->with('error', 'No unpaid registrations found in your selection.');
        }

        // Calculate fees
        $finePerStudent = $school->getFinePerStudentAmount();
        $isFineApplicable = $school->isFineApplicable();

        $baseTotal = 0.00;
        $fineTotal = 0.00;
        $totalAmount = 0.00;
        $classBreakdown = [];

        foreach ($students as $student) {
            $classId = $student->class_id;
            $className = $student->class->name;
            $baseFee = $student->registration_fee;
            $studentFine = $finePerStudent;
            $fee = $baseFee + $studentFine;

            $baseTotal += $baseFee;
            $fineTotal += $studentFine;
            $totalAmount += $fee;

            if (! isset($classBreakdown[$classId])) {
                $classBreakdown[$classId] = [
                    'name' => $className,
                    'count' => 0,
                    'fee' => $fee,
                    'base_fee' => $baseFee,
                    'fine_fee' => $studentFine,
                    'total' => 0.00,
                ];
            }
            $classBreakdown[$classId]['count']++;
            $classBreakdown[$classId]['total'] += $fee;
        }

        return view('school-admin.payments.checkout', compact(
            'students',
            'baseTotal',
            'fineTotal',
            'totalAmount',
            'finePerStudent',
            'isFineApplicable',
            'classBreakdown'
        ));
    }

    /**
     * School Admin: Create a Cashfree order and redirect to checkout with session details.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function initiate(Request $request)
    {
        if (Examination::isRegistrationClosed()) {
            return redirect()->route('school.students.index')->with('error', 'Registration is closed for this examination session. Payments for drafted candidates are no longer accepted.');
        }

        $studentIds = $request->input('student_ids');

        if (empty($studentIds) || ! is_array($studentIds)) {
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

        try {
            $sessionData = $this->createOrRetrieveCashfreeSession($school, $studentIds);
        } catch (\Exception $e) {
            return redirect()->route('school.students.index')
                ->with('error', 'Could not initiate payment: '.$e->getMessage());
        }

        $cfOrderId = $sessionData['cfOrderId'];
        $paymentDbId = $sessionData['paymentDbId'];
        $paymentSessionId = $sessionData['paymentSessionId'];
        $totalAmount = $sessionData['totalAmount'];
        $students = $sessionData['students'];

        // Reload the checkout view with Cashfree session details
        $finePerStudent = $school->getFinePerStudentAmount();
        $isFineApplicable = $school->isFineApplicable();

        $baseTotal = 0.00;
        $fineTotal = 0.00;
        $classBreakdown = [];

        foreach ($students as $student) {
            $classId = $student->class_id;
            $className = $student->class->name;
            $baseFee = $student->registration_fee;
            $studentFine = $finePerStudent;
            $fee = $baseFee + $studentFine;

            $baseTotal += $baseFee;
            $fineTotal += $studentFine;

            if (! isset($classBreakdown[$classId])) {
                $classBreakdown[$classId] = [
                    'name' => $className,
                    'count' => 0,
                    'fee' => $fee,
                    'base_fee' => $baseFee,
                    'fine_fee' => $studentFine,
                    'total' => 0.00,
                ];
            }
            $classBreakdown[$classId]['count']++;
            $classBreakdown[$classId]['total'] += $fee;
        }

        return view('school-admin.payments.checkout', compact(
            'students',
            'baseTotal',
            'fineTotal',
            'totalAmount',
            'finePerStudent',
            'isFineApplicable',
            'classBreakdown'
        ))->with([
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
     * Helper to create a new Cashfree order or retrieve an existing active session.
     *
     * @param  School  $school
     * @param  array  $studentIds
     * @return array
     */
    private function createOrRetrieveCashfreeSession(School $school, array $studentIds): array
    {
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

        if ($matchingPayment) {
            $cfOrderId = $matchingPayment->cashfree_order_id;
            $paymentDbId = $matchingPayment->id;

            // Verify existing session against Cashfree
            try {
                $http = new GuzzleClient(['timeout' => 15]);
                $cfResponse = $http->get($baseUrl.'/orders/'.$cfOrderId, [
                    'headers' => $headers,
                ]);
                $cfOrder = json_decode((string) $cfResponse->getBody(), true);
                if (isset($cfOrder['payment_session_id']) && ($cfOrder['order_status'] ?? '') === 'ACTIVE') {
                    return [
                        'cfOrderId' => $cfOrderId,
                        'paymentDbId' => $paymentDbId,
                        'paymentSessionId' => $cfOrder['payment_session_id'],
                        'totalAmount' => $matchingPayment->amount,
                        'students' => $matchingPayment->students,
                    ];
                } else {
                    $matchingPayment->status = 'Failed';
                    $matchingPayment->save();
                }
            } catch (\Exception $e) {
                $matchingPayment->status = 'Failed';
                $matchingPayment->save();
            }
        }

        // Initiate a new payment session inside a database transaction
        return DB::transaction(function () use ($school, $studentIds, $baseUrl, $headers) {
            $students = Student::where('school_id', $school->id)
                ->whereIn('id', $studentIds)
                ->where('payment_status', 'Unpaid')
                ->lockForUpdate()
                ->get();

            if ($students->isEmpty() || $students->count() !== count(array_unique($studentIds))) {
                throw new \Exception('One or more selected students are invalid, already paid, or do not belong to your school.');
            }

            // Cancel/Fail older pending payments with overlapping student IDs
            $overlappingPaymentIds = DB::table('payment_student')
                ->join('payments', 'payment_student.payment_id', '=', 'payments.id')
                ->where('payments.school_id', $school->id)
                ->where('payments.status', 'Pending')
                ->whereIn('payment_student.student_id', $studentIds)
                ->pluck('payments.id')
                ->unique();

            if ($overlappingPaymentIds->isNotEmpty()) {
                Payment::whereIn('id', $overlappingPaymentIds)->update(['status' => 'Failed']);
            }

            $finePerStudent = $school->getFinePerStudentAmount();
            $baseAmount = 0.00;
            $fineAmount = 0.00;

            foreach ($students as $student) {
                $baseAmount += $student->registration_fee;
                $fineAmount += $finePerStudent;
            }
            $totalAmount = $baseAmount + $fineAmount;

            $cfOrderId = 'ERMS_'.strtoupper(bin2hex(random_bytes(8)));
            $returnUrl = route('school.payments.callback').'?order_id='.$cfOrderId;

            $phoneDigits = preg_replace('/[^0-9]/', '', $school->mobile_number ?? '');
            if (strlen($phoneDigits) === 12 && str_starts_with($phoneDigits, '91')) {
                $phoneDigits = substr($phoneDigits, 2);
            }
            $customerPhone = (strlen($phoneDigits) >= 10 && strlen($phoneDigits) <= 15) ? $phoneDigits : '9999999999';

            $payload = [
                'order_id' => $cfOrderId,
                'order_amount' => round($totalAmount, 2),
                'order_currency' => 'INR',
                'order_note' => 'YES GENIUS — Registration Fee for '.$school->name,
                'customer_details' => [
                    'customer_id' => 'school_'.$school->id,
                    'customer_phone' => $customerPhone,
                    'customer_email' => Auth::user()->email,
                    'customer_name' => Auth::user()->name,
                ],
                'order_meta' => [
                    'return_url' => $returnUrl,
                ],
            ];

            $http = new GuzzleClient(['timeout' => 15]);
            $cfResponse = $http->post($baseUrl.'/orders', [
                'headers' => $headers,
                'json' => $payload,
            ]);
            $cfOrder = json_decode((string) $cfResponse->getBody(), true);
            $paymentSessionId = $cfOrder['payment_session_id'] ?? null;

            if (! $paymentSessionId) {
                throw new \Exception('Failed to get payment session ID from Cashfree.');
            }

            $payment = Payment::create([
                'school_id' => $school->id,
                'cashfree_order_id' => $cfOrderId,
                'amount' => $totalAmount,
                'base_amount' => $baseAmount,
                'fine_amount' => $fineAmount,
                'payment_method' => 'Cashfree',
                'status' => 'Pending',
                'paid_at' => null,
            ]);

            foreach ($students as $student) {
                $stBase = $student->registration_fee;
                $stFine = $finePerStudent;
                $stTotal = $stBase + $stFine;

                $payment->students()->attach($student->id, [
                    'amount' => $stTotal,
                    'base_amount' => $stBase,
                    'fine_amount' => $stFine,
                ]);
            }

            return [
                'cfOrderId' => $cfOrderId,
                'paymentDbId' => $payment->id,
                'paymentSessionId' => $paymentSessionId,
                'totalAmount' => $totalAmount,
                'students' => $students,
            ];
        });
    }

    /**
     * School Admin: Handle Cashfree payment return (GET redirect from Cashfree hosted page).
     * Verifies payment by fetching order status from Cashfree API.
     */
    public function callback(Request $request)
    {
        $cfOrderId = $request->query('order_id');

        if (! $cfOrderId) {
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

            if (! $payment) {
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
            $cfResponse = $http->get($baseUrl.'/orders/'.$cfOrderId, ['headers' => $headers]);
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
                    ->log('Cashfree: Payment not completed. Order status: '.$orderStatus.'. Payment marked as Failed.');
            });

            return redirect()->route('school.students.index')
                ->with('error', 'Payment was not completed (status: '.$orderStatus.'). No amount was charged.');
        }

        // Payment successful — fetch the CF payment ID and actual payment method
        $cfPaymentId = null;
        $resolvedMethod = 'Cashfree'; // fallback
        try {
            $paymentsResponse = $http->get($baseUrl.'/orders/'.$cfOrderId.'/payments', ['headers' => $headers]);
            $cfPayments = json_decode((string) $paymentsResponse->getBody(), true);
            if (! empty($cfPayments) && isset($cfPayments[0]['cf_payment_id'])) {
                $cfPaymentId = (string) $cfPayments[0]['cf_payment_id'];
                $resolvedMethod = self::resolveCashfreePaymentMethod($cfPayments[0]);
            }
        } catch (RequestException $e) {
            // Non-critical — fall back to order ID if payment ID call fails
        }

        // Mark payment as complete
        DB::transaction(function () use ($payment, $cfOrderId, $cfPaymentId, $resolvedMethod) {
            // Re-lock the payment row inside the transaction to prevent TOCTOU race with webhook (CWE-367)
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->first();

            if (! $lockedPayment || $lockedPayment->status === 'Paid') {
                return; // Already processed by webhook
            }

            $lockedPayment->status = 'Paid';
            $lockedPayment->transaction_id = $cfPaymentId ?? $cfOrderId;
            $lockedPayment->cashfree_payment_id = $cfPaymentId;
            $lockedPayment->payment_method = $resolvedMethod;
            $lockedPayment->paid_at = now();
            $lockedPayment->save();

            // Mark all attached students as paid and submitted
            foreach ($lockedPayment->students as $student) {
                $student->payment_status = 'Paid';
                $student->status = 'Submitted';
                $student->save();

                activity()
                    ->performedOn($student)
                    ->log('Registration payment completed via Cashfree. Status updated to Submitted.');
            }

            activity()
                ->performedOn($lockedPayment)
                ->log('Cashfree: Order '.$cfOrderId.' verified PAID. ₹'.number_format($lockedPayment->amount, 2).' collected.');
        });

        return redirect()->route('school.payments.receipt', $payment->id)
            ->with('success', 'Payment of ₹'.number_format($payment->amount, 2).' collected successfully! Candidates submitted to the board.');
    }

    /**
     * Cashfree Webhook: Handle async payment notifications.
     * CWE-345: Verifies HMAC-SHA256 signature before processing.
     * This route is exempt from CSRF (see bootstrap/app.php).
     */
    public function webhook(Request $request)
    {
        $webhookSecret = config('services.cashfree.webhook_secret');

        if (! $webhookSecret) {
            Log::critical('CASHFREE_WEBHOOK_SECRET not configured — rejecting webhook');
            abort(500, 'Webhook verification not configured');
        }

        // Verify signature: Cashfree sends x-webhook-signature and x-webhook-timestamp
        $timestamp = $request->header('x-webhook-timestamp');
        $signature = $request->header('x-webhook-signature');
        $rawBody = $request->getContent();

        if (! $timestamp || ! $signature) {
            return response()->json(['status' => 'error', 'message' => 'Missing webhook signature headers'], 401);
        }

        $signedPayload = $timestamp.$rawBody;
        $expectedSig = base64_encode(hash_hmac('sha256', $signedPayload, $webhookSecret, true));

        if (! hash_equals($expectedSig, $signature)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook signature'], 401);
        }

        $payload = $request->json()->all();
        $eventType = $payload['type'] ?? null;
        $data = $payload['data'] ?? [];
        $orderData = $data['order'] ?? [];
        $cfOrderId = $orderData['order_id'] ?? null;

        // Only process PAYMENT_SUCCESS events
        if ($eventType !== 'PAYMENT_SUCCESS' || ! $cfOrderId) {
            return response()->json(['status' => 'ok', 'message' => 'Event ignored']);
        }

        $payment = Payment::where('cashfree_order_id', $cfOrderId)
            ->where('status', 'Pending')
            ->first();

        if (! $payment) {
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

            if (! $payment || $payment->status === 'Paid') {
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
                ->log('Cashfree webhook: Order '.$cfOrderId.' confirmed PAID. ₹'.number_format($payment->amount, 2).' collected.');
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

                return $upiId ? 'UPI ('.$upiId.')' : 'UPI';

            case 'card':
                $card = $method['card'] ?? [];
                $cardType = ucfirst(strtolower($card['card_type'] ?? '')); // DEBIT / CREDIT
                $network = ucfirst(strtolower($card['card_network'] ?? '')); // VISA / MASTERCARD
                $last4 = $card['card_number'] ?? null; // last 4 digits if available
                $label = trim($network.' '.$cardType.' Card');
                if ($last4) {
                    $label .= ' (···'.substr($last4, -4).')';
                }

                return $label ?: 'Card';

            case 'net_banking':
            case 'netbanking':
                $bankName = $method['netbanking']['channel'] ?? $method['netbanking']['netbanking_bank_code'] ?? null;

                return $bankName ? 'Net Banking ('.$bankName.')' : 'Net Banking';

            case 'wallet':
                $walletName = $method['app']['channel'] ?? null;

                return $walletName ? ucfirst($walletName).' Wallet' : 'Wallet';

            case 'emi':
                $emiCard = $method['emi']['card_network'] ?? null;

                return $emiCard ? 'EMI ('.ucfirst(strtolower($emiCard)).')' : 'EMI';

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
     * Super Admin: View Payment Receipt for any transaction.
     */
    public function adminReceipt(Payment $payment)
    {
        $payment->load(['students.class', 'school']);

        return view('school-admin.payments.receipt', compact('payment'));
    }

    /**
     * Super Admin: Global Payouts & Payments Report.
     */
    public function adminIndex(Request $request)
    {
        $selectedSchool = $request->filled('school_id') ? School::find($request->school_id) : null;

        $query = Payment::with(['school', 'students.class']);

        // Filter by school
        if ($selectedSchool) {
            $query->where('school_id', $selectedSchool->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', $request->date)
                    ->orWhereDate('created_at', $request->date);
            });
        }
        if ($request->filled('start_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', '>=', $request->start_date)
                    ->orWhereDate('created_at', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', '<=', $request->end_date)
                    ->orWhereDate('created_at', '<=', $request->end_date);
            });
        }

        // Search Transaction ID
        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where('transaction_id', 'like', "%{$search}%");
        }

        $payments = $query->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderBy('id', 'DESC')
            ->paginate(20)
            ->withQueryString();

        // Metrics base query
        $metricPaymentsQuery = Payment::query();
        if ($selectedSchool) {
            $metricPaymentsQuery->where('school_id', $selectedSchool->id);
        }

        $totalCollected = (clone $metricPaymentsQuery)->where('status', 'Paid')->sum('amount');
        $totalBaseCollected = (clone $metricPaymentsQuery)->where('status', 'Paid')->sum('base_amount');
        $totalFineCollected = (clone $metricPaymentsQuery)->where('status', 'Paid')->sum('fine_amount');

        // Fallback for past payments where base_amount was 0 but amount > 0
        if ($totalBaseCollected == 0 && $totalCollected > 0) {
            $totalBaseCollected = $totalCollected - $totalFineCollected;
        }

        // Outstanding calculations (Draft and unpaid students across active classes/categories)
        $unpaidQuery = Student::with(['school', 'category', 'class'])
            ->where('payment_status', 'Unpaid')
            ->whereNull('deleted_at');

        if ($selectedSchool) {
            $unpaidQuery->where('school_id', $selectedSchool->id);
        }

        $unpaidStudents = $unpaidQuery->get();

        $totalOutstandingBase = 0.00;
        $totalOutstandingFine = 0.00;

        foreach ($unpaidStudents as $st) {
            $base = $st->registration_fee ?? 0;
            $fine = $st->fine_amount ?? 0;
            $totalOutstandingBase += $base;
            $totalOutstandingFine += $fine;
        }

        $totalOutstanding = $totalOutstandingBase + $totalOutstandingFine;

        $paymentsCount = (clone $metricPaymentsQuery)->count();
        $activeSchoolsPaid = $selectedSchool
            ? ((clone $metricPaymentsQuery)->where('status', 'Paid')->exists() ? 1 : 0)
            : School::whereHas('payments', function ($q) {
                $q->where('status', 'Paid');
            })->count();

        $schools = School::where('status', true)->orderBy('name')->get();

        return view('super-admin.payments.index', compact(
            'payments',
            'schools',
            'selectedSchool',
            'totalCollected',
            'totalBaseCollected',
            'totalFineCollected',
            'totalOutstanding',
            'totalOutstandingBase',
            'totalOutstandingFine',
            'paymentsCount',
            'activeSchoolsPaid'
        ));
    }

    /**
     * Super Admin: Export Payouts Report to CSV.
     */
    public function adminExport(Request $request)
    {
        $query = Payment::with(['school', 'students'])->withCount('students');

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', $request->date)
                    ->orWhereDate('created_at', $request->date);
            });
        }
        if ($request->filled('start_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', '>=', $request->start_date)
                    ->orWhereDate('created_at', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', '<=', $request->end_date)
                    ->orWhereDate('created_at', '<=', $request->end_date);
            });
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where('transaction_id', 'like', "%{$search}%");
        }

        $payments = $query->orderByRaw('COALESCE(paid_at, created_at) DESC')->orderBy('id', 'DESC')->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=erms_payouts_report_'.time().'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Date', 'School Name', 'School Code', 'Transaction ID', 'Method', 'Candidates Count', 'Base Amount (INR)', 'Fine Amount (INR)', 'Total Amount (INR)', 'Status'];

        $callback = function () use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $sanitizeCsvField = function ($value) {
                if (is_null($value)) {
                    return '';
                }
                $value = (string) $value;
                if (strlen($value) > 0 && in_array(substr($value, 0, 1), ['=', '+', '-', '@'])) {
                    return "'".$value;
                }

                return $value;
            };

            /** @var Payment $payment */
            foreach ($payments as $payment) {
                $baseFee = (float) ($payment->base_amount > 0 ? $payment->base_amount : ($payment->amount - $payment->fine_amount));
                $txDate = ($payment->paid_at ?? $payment->created_at)->format('Y-m-d H:i:s');
                $candidatesCount = $payment->students_count ?? ($payment->students ? $payment->students->count() : 0);
                fputcsv($file, [
                    $txDate,
                    $sanitizeCsvField($payment->school->name ?? 'N/A'),
                    $sanitizeCsvField($payment->school->code ?? 'N/A'),
                    $sanitizeCsvField($payment->transaction_id ?? $payment->cashfree_order_id ?? ''),
                    $sanitizeCsvField($payment->payment_method ?? 'ONLINE'),
                    $candidatesCount,
                    number_format((float) $baseFee, 2, '.', ''),
                    number_format((float) ($payment->fine_amount ?? 0), 2, '.', ''),
                    number_format((float) ($payment->amount ?? 0), 2, '.', ''),
                    $sanitizeCsvField($payment->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Super Admin: Export School Financial Report to PDF.
     */
    public function adminExportPdf(Request $request)
    {
        $query = Payment::with(['school', 'students.class']);

        $selectedSchool = $request->filled('school_id') ? School::find($request->school_id) : null;
        if ($selectedSchool) {
            $query->where('school_id', $selectedSchool->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', $request->date)
                    ->orWhereDate('created_at', $request->date);
            });
        }
        if ($request->filled('start_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', '>=', $request->start_date)
                    ->orWhereDate('created_at', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('paid_at', '<=', $request->end_date)
                    ->orWhereDate('created_at', '<=', $request->end_date);
            });
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where('transaction_id', 'like', "%{$search}%");
        }

        $payments = $query->orderByRaw('COALESCE(paid_at, created_at) DESC')->orderBy('id', 'DESC')->get();

        // Build active filters summary for display in PDF
        $filterDetails = [];
        if ($selectedSchool) {
            $filterDetails[] = 'School: ' . $selectedSchool->name . ' (' . $selectedSchool->code . ')';
        }
        if ($request->filled('status')) {
            $filterDetails[] = 'Status: ' . $request->status;
        }
        if ($request->filled('date')) {
            $filterDetails[] = 'Date: ' . \Carbon\Carbon::parse($request->date)->format('d M Y');
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filterDetails[] = 'Period: ' . \Carbon\Carbon::parse($request->start_date)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($request->end_date)->format('d M Y');
        } elseif ($request->filled('start_date')) {
            $filterDetails[] = 'From: ' . \Carbon\Carbon::parse($request->start_date)->format('d M Y');
        } elseif ($request->filled('end_date')) {
            $filterDetails[] = 'To: ' . \Carbon\Carbon::parse($request->end_date)->format('d M Y');
        }
        if ($request->filled('search')) {
            $filterDetails[] = 'Search: ' . $request->search;
        }

        // Metrics for Single School vs Multi-School
        if ($selectedSchool) {
            $totalCollected = Payment::where('school_id', $selectedSchool->id)->where('status', 'Paid')->sum('amount');
            $totalBaseCollected = Payment::where('school_id', $selectedSchool->id)->where('status', 'Paid')->sum('base_amount');
            $totalFineCollected = Payment::where('school_id', $selectedSchool->id)->where('status', 'Paid')->sum('fine_amount');
            if ($totalBaseCollected == 0 && $totalCollected > 0) {
                $totalBaseCollected = $totalCollected - $totalFineCollected;
            }

            $unpaidStudents = Student::where('school_id', $selectedSchool->id)
                ->where('payment_status', 'Unpaid')
                ->whereNull('deleted_at')
                ->get();

            $totalOutstanding = $unpaidStudents->sum(function ($st) {
                return ($st->registration_fee ?? 0) + ($st->fine_amount ?? 0);
            });
            $totalOutstandingBase = $unpaidStudents->sum('registration_fee');
            $totalOutstandingFine = $unpaidStudents->sum('fine_amount');
            $unpaidCount = $unpaidStudents->count();

            $paidStudentsCount = Student::where('school_id', $selectedSchool->id)
                ->where('payment_status', 'Paid')
                ->whereNull('deleted_at')
                ->count();

            $title = $selectedSchool->name . ' (' . $selectedSchool->code . ') - Financial Report';
            $fileName = 'financial_report_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $selectedSchool->code) . '_' . date('Ymd_His') . '.pdf';
        } else {
            $totalCollected = $payments->where('status', 'Paid')->sum('amount');
            $totalBaseCollected = $payments->where('status', 'Paid')->sum('base_amount');
            $totalFineCollected = $payments->where('status', 'Paid')->sum('fine_amount');
            if ($totalBaseCollected == 0 && $totalCollected > 0) {
                $totalBaseCollected = $totalCollected - $totalFineCollected;
            }

            $totalOutstanding = 0;
            $totalOutstandingBase = 0;
            $totalOutstandingFine = 0;
            $unpaidCount = 0;
            $paidStudentsCount = 0;

            $title = 'Consolidated School Financial Report';
            $fileName = 'consolidated_financial_report_' . date('Ymd_His') . '.pdf';
        }

        // Group payments by school
        $groupedPayments = $payments->groupBy('school_id');

        $adminName = Auth::user()?->name ?? 'Authorized Super Administrator';

        $pdf = Pdf::loadView('pdf.payments-report', compact(
            'payments',
            'groupedPayments',
            'selectedSchool',
            'totalCollected',
            'totalBaseCollected',
            'totalFineCollected',
            'totalOutstanding',
            'totalOutstandingBase',
            'totalOutstandingFine',
            'unpaidCount',
            'paidStudentsCount',
            'filterDetails',
            'title',
            'adminName'
        ));

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('isFontSubsettingEnabled', false);
        $pdf->setOption('defaultFont', 'Helvetica');

        return $pdf->download($fileName);
    }
}
