@php
    if (!function_exists('paymentsReportImageToBase64')) {
        function paymentsReportImageToBase64($path)
        {
            if ($path && file_exists($path)) {
                try {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                } catch (\Exception $e) {
                    return null;
                }
            }
            return null;
        }
    }

    $leftLogoPath = file_exists(public_path('logob_pdf.png')) 
        ? public_path('logob_pdf.png') 
        : (file_exists(public_path('logob.png')) ? public_path('logob.png') : null);
    $rightLogoPath = file_exists(public_path('logo_pdf.png')) 
        ? public_path('logo_pdf.png') 
        : (file_exists(public_path('logo.png')) ? public_path('logo.png') : null);

    $leftLogoBase64 = paymentsReportImageToBase64($leftLogoPath);
    $rightLogoBase64 = paymentsReportImageToBase64($rightLogoPath);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20px 25px 25px 25px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 9.5px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .logo-cell {
            width: 70px;
            vertical-align: middle;
        }
        .logo-img {
            max-width: 65px;
            max-height: 50px;
        }
        .header-title-cell {
            vertical-align: middle;
            text-align: center;
        }
        .org-name {
            font-size: 13px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .doc-title {
            font-size: 11px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .meta-cell {
            width: 140px;
            vertical-align: middle;
            text-align: right;
            font-size: 8px;
            color: #64748b;
            line-height: 1.4;
        }
        .school-profile-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .school-profile-table {
            width: 100%;
            border-collapse: collapse;
        }
        .school-profile-table td {
            padding: 2px 4px;
            font-size: 9px;
            vertical-align: top;
        }
        .profile-label {
            font-weight: bold;
            color: #475569;
            width: 110px;
            text-transform: uppercase;
            font-size: 8px;
        }
        .profile-val {
            color: #0f172a;
            font-weight: 600;
        }
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }
        .kpi-card {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .kpi-label {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
        }
        .kpi-value {
            font-size: 13px;
            font-weight: 800;
            margin-top: 2px;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .kpi-val-green { color: #059669; }
        .kpi-val-blue { color: #2563eb; }
        .kpi-val-amber { color: #d97706; }
        .kpi-val-rose { color: #e11d48; }
        .kpi-val-slate { color: #1e293b; }

        .filters-badge-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            padding: 4px 8px;
            margin-bottom: 10px;
            font-size: 8px;
            color: #475569;
        }
        .filter-chip {
            display: inline-block;
            background: #e2e8f0;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 600;
            margin-right: 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 5px;
            border: 1px solid #1e293b;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 5px;
            font-size: 8.5px;
            color: #334155;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono { font-family: 'Courier New', Courier, monospace; font-size: 8px; }
        .font-bold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-failed {
            background-color: #ffe4e6;
            color: #9f1239;
            border: 1px solid #fecdd3;
        }

        .section-header-bar {
            background-color: #2563eb;
            color: #ffffff;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
            margin-top: 10px;
        }
        .school-subtotal-row td {
            background-color: #eff6ff !important;
            font-weight: bold;
            color: #1e3a8a;
            border-top: 1.5px solid #bfdbfe;
        }
        .grand-total-row td {
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #0f172a;
        }

        .footer-note {
            margin-top: 15px;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 7.5px;
            color: #94a3b8;
            width: 100%;
        }
        .footer-table {
            width: 100%;
        }
        .footer-table td {
            font-size: 7.5px;
            color: #64748b;
            vertical-align: top;
        }
    </style>
</head>
<body>

    {{-- Official Header --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($leftLogoBase64)
                    <img src="{{ $leftLogoBase64 }}" class="logo-img" alt="Logo">
                @endif
            </td>
            <td class="header-title-cell">
                <div class="org-name">YES GENIUS TALENT SEARCH EXAMINATION</div>
                <div class="doc-title">{{ $title }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 1px;">Official Payouts & Financial Transaction Statement</div>
            </td>
            <td class="meta-cell">
                <div><strong>Generated:</strong> {{ date('d M Y, h:i A') }}</div>
                <div><strong>Total Txns:</strong> {{ $payments->count() }}</div>
                @if($selectedSchool)
                    <div><strong>Code:</strong> {{ $selectedSchool->code }}</div>
                @endif
            </td>
            <td class="logo-cell" style="text-align: right;">
                @if($rightLogoBase64)
                    <img src="{{ $rightLogoBase64 }}" class="logo-img" alt="Logo">
                @endif
            </td>
        </tr>
    </table>

    {{-- Active Filters Notice (if applied) --}}
    @if(!empty($filterDetails) && count($filterDetails) > 0)
        <div class="filters-badge-box">
            <strong style="color: #1e293b; text-transform: uppercase;">Active Filters:</strong>
            @foreach($filterDetails as $filterItem)
                <span class="filter-chip">{{ $filterItem }}</span>
            @endforeach
        </div>
    @endif

    {{-- IF SINGLE SCHOOL FINANCIAL REPORT --}}
    @if($selectedSchool)
        {{-- School Information Card --}}
        <div class="school-profile-card">
            <table class="school-profile-table">
                <tr>
                    <td class="profile-label">School Name:</td>
                    <td class="profile-val" colspan="3">{{ $selectedSchool->name }}</td>
                    <td class="profile-label">School Code:</td>
                    <td class="profile-val">{{ $selectedSchool->code }}</td>
                </tr>
                <tr>
                    <td class="profile-label">Zone:</td>
                    <td class="profile-val">{{ $selectedSchool->zone ?? 'Unassigned' }}</td>
                    <td class="profile-label">State:</td>
                    <td class="profile-val">{{ $selectedSchool->state ?? 'N/A' }}</td>
                    <td class="profile-label">Contact Person:</td>
                    <td class="profile-val">{{ $selectedSchool->contact_person ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="profile-label">Mobile Number:</td>
                    <td class="profile-val">{{ $selectedSchool->mobile_number ?? 'N/A' }}</td>
                    <td class="profile-label">Email:</td>
                    <td class="profile-val">{{ $selectedSchool->email ?? 'N/A' }}</td>
                    <td class="profile-label">Address:</td>
                    <td class="profile-val">{{ $selectedSchool->address ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        {{-- School KPI Financial Cards --}}
        <table class="kpi-table">
            <tr>
                <td class="kpi-card" style="width: 20%;">
                    <div class="kpi-label">Total Amount Paid</div>
                    <div class="kpi-value kpi-val-green">Rs. {{ number_format($totalCollected, 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 20%;">
                    <div class="kpi-label">Base Registration Fees</div>
                    <div class="kpi-value kpi-val-blue">Rs. {{ number_format($totalBaseCollected, 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 20%;">
                    <div class="kpi-label">Late Fine Fees</div>
                    <div class="kpi-value kpi-val-amber">Rs. {{ number_format($totalFineCollected, 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 20%;">
                    <div class="kpi-label">Outstanding Balance</div>
                    <div class="kpi-value kpi-val-rose">Rs. {{ number_format($totalOutstanding, 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 20%;">
                    <div class="kpi-label">Candidate Volume</div>
                    <div class="kpi-value kpi-val-slate">{{ $paidStudentsCount }} <span style="font-size: 8px; font-weight: normal; color: #64748b;">paid</span> / {{ $unpaidCount }} <span style="font-size: 8px; font-weight: normal; color: #e11d48;">due</span></div>
                </td>
            </tr>
        </table>

        {{-- Single School Transactions Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 28px;" class="text-center">#</th>
                    <th style="width: 110px;">Date & Time</th>
                    <th style="width: 140px;">Transaction ID / Order ID</th>
                    <th style="width: 75px;">Method</th>
                    <th style="width: 65px;" class="text-center">Candidates</th>
                    <th style="width: 85px;" class="text-right">Base Fee (INR)</th>
                    <th style="width: 85px;" class="text-right">Fine (INR)</th>
                    <th style="width: 95px;" class="text-right">Total (INR)</th>
                    <th style="width: 65px;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumCandidates = 0;
                    $sumBase = 0;
                    $sumFine = 0;
                    $sumTotal = 0;
                @endphp
                @forelse($payments as $index => $payment)
                    @php
                        $baseFee = $payment->base_amount > 0 ? $payment->base_amount : max(0, $payment->amount - $payment->fine_amount);
                        $cCount = $payment->students_count ?? $payment->students->count();
                        if ($payment->status === 'Paid') {
                            $sumCandidates += $cCount;
                            $sumBase += $baseFee;
                            $sumFine += $payment->fine_amount;
                            $sumTotal += $payment->amount;
                        }
                        $txDate = ($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A');
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $txDate }}</td>
                        <td class="font-mono">{{ $payment->transaction_id ?? $payment->cashfree_order_id ?? 'N/A' }}</td>
                        <td>{{ strtoupper($payment->payment_method ?? 'ONLINE') }}</td>
                        <td class="text-center font-bold">{{ $cCount }}</td>
                        <td class="text-right font-mono">{{ number_format($baseFee, 2) }}</td>
                        <td class="text-right font-mono" style="color: #b45309;">{{ number_format($payment->fine_amount, 2) }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-center">
                            @if($payment->status === 'Paid')
                                <span class="badge badge-paid">Paid</span>
                            @elseif($payment->status === 'Pending')
                                <span class="badge badge-pending">Pending</span>
                            @else
                                <span class="badge badge-failed">Failed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #94a3b8;">
                            No financial transactions found for this school matching the filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($payments->count() > 0)
                <tfoot>
                    <tr class="school-subtotal-row">
                        <td colspan="4" class="text-right" style="text-transform: uppercase;">Total Paid Transactions Summary:</td>
                        <td class="text-center font-bold">{{ $sumCandidates }}</td>
                        <td class="text-right font-mono">{{ number_format($sumBase, 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($sumFine, 2) }}</td>
                        <td class="text-right font-mono font-bold" style="color: #065f46;">Rs. {{ number_format($sumTotal, 2) }}</td>
                        <td class="text-center font-bold" style="color: #065f46;">COMPLETED</td>
                    </tr>
                </tfoot>
            @endif
        </table>

    {{-- IF CONSOLIDATED / MULTI-SCHOOL FINANCIAL REPORT --}}
    @else
        {{-- Overall Financial KPI Cards --}}
        <table class="kpi-table">
            <tr>
                <td class="kpi-card" style="width: 25%;">
                    <div class="kpi-label">Total Revenue Collected</div>
                    <div class="kpi-value kpi-val-green">Rs. {{ number_format($totalCollected, 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 25%;">
                    <div class="kpi-label">Total Base Registration Fees</div>
                    <div class="kpi-value kpi-val-blue">Rs. {{ number_format($totalBaseCollected, 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 25%;">
                    <div class="kpi-label">Total Late Fine Penalties</div>
                    <div class="kpi-value kpi-val-amber">Rs. {{ number_format($totalFineCollected, 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 25%;">
                    <div class="kpi-label">Total Contributing Schools</div>
                    <div class="kpi-value kpi-val-slate">{{ $groupedPayments->count() }} <span style="font-size: 8px; font-weight: normal; color: #64748b;">schools ({{ $payments->count() }} txns)</span></div>
                </td>
            </tr>
        </table>

        @php
            $grandCandidates = 0;
            $grandBase = 0;
            $grandFine = 0;
            $grandTotal = 0;
        @endphp

        {{-- Grouped by each school --}}
        @forelse($groupedPayments as $schoolId => $schoolPayments)
            @php
                $school = $schoolPayments->first()->school;
                $schoolPaidTxns = $schoolPayments->where('status', 'Paid');
                $schoolPaidTotal = $schoolPaidTxns->sum('amount');
                $schoolPaidBase = $schoolPaidTxns->sum('base_amount');
                $schoolPaidFine = $schoolPaidTxns->sum('fine_amount');
                if ($schoolPaidBase == 0 && $schoolPaidTotal > 0) {
                    $schoolPaidBase = $schoolPaidTotal - $schoolPaidFine;
                }
                $schoolCandidates = $schoolPaidTxns->sum(function($p) {
                    return $p->students_count ?? $p->students->count();
                });

                $grandCandidates += $schoolCandidates;
                $grandBase += $schoolPaidBase;
                $grandFine += $schoolPaidFine;
                $grandTotal += $schoolPaidTotal;
            @endphp

            <div class="section-header-bar">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="color: #ffffff; font-weight: bold; font-size: 9.5px;">
                            {{ $school->name ?? 'Unknown School' }} &nbsp; 
                            <span style="background: rgba(255,255,255,0.25); padding: 1px 5px; border-radius: 3px; font-size: 8px;">
                                Code: {{ $school->code ?? 'N/A' }}
                            </span>
                            @if(!empty($school->zone))
                                &nbsp; <span style="font-size: 8px; opacity: 0.9;">[Zone: {{ $school->zone }}]</span>
                            @endif
                        </td>
                        <td style="text-align: right; color: #ffffff; font-size: 8.5px;">
                            Paid Volume: <strong>Rs. {{ number_format($schoolPaidTotal, 2) }}</strong> &nbsp;|&nbsp; 
                            Candidates: <strong>{{ $schoolCandidates }}</strong>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="data-table" style="margin-top: 0; margin-bottom: 14px;">
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center">#</th>
                        <th style="width: 105px;">Date & Time</th>
                        <th style="width: 140px;">Transaction ID / Order ID</th>
                        <th style="width: 70px;">Method</th>
                        <th style="width: 60px;" class="text-center">Candidates</th>
                        <th style="width: 80px;" class="text-right">Base Fee (INR)</th>
                        <th style="width: 75px;" class="text-right">Fine (INR)</th>
                        <th style="width: 90px;" class="text-right">Total (INR)</th>
                        <th style="width: 65px;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schoolPayments as $idx => $payment)
                        @php
                            $baseFee = $payment->base_amount > 0 ? $payment->base_amount : max(0, $payment->amount - $payment->fine_amount);
                            $cCount = $payment->students_count ?? $payment->students->count();
                            $txDate = ($payment->paid_at ?? $payment->created_at)->format('d M Y, h:i A');
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $txDate }}</td>
                            <td class="font-mono">{{ $payment->transaction_id ?? $payment->cashfree_order_id ?? 'N/A' }}</td>
                            <td>{{ strtoupper($payment->payment_method ?? 'ONLINE') }}</td>
                            <td class="text-center font-bold">{{ $cCount }}</td>
                            <td class="text-right font-mono">{{ number_format($baseFee, 2) }}</td>
                            <td class="text-right font-mono" style="color: #b45309;">{{ number_format($payment->fine_amount, 2) }}</td>
                            <td class="text-right font-mono font-bold">{{ number_format($payment->amount, 2) }}</td>
                            <td class="text-center">
                                @if($payment->status === 'Paid')
                                    <span class="badge badge-paid">Paid</span>
                                @elseif($payment->status === 'Pending')
                                    <span class="badge badge-pending">Pending</span>
                                @else
                                    <span class="badge badge-failed">Failed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="school-subtotal-row">
                        <td colspan="4" class="text-right" style="font-size: 8px;">Subtotal for {{ $school->name ?? 'School' }} (Paid):</td>
                        <td class="text-center font-bold">{{ $schoolCandidates }}</td>
                        <td class="text-right font-mono">{{ number_format($schoolPaidBase, 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($schoolPaidFine, 2) }}</td>
                        <td class="text-right font-mono font-bold" style="color: #065f46;">Rs. {{ number_format($schoolPaidTotal, 2) }}</td>
                        <td class="text-center">—</td>
                    </tr>
                </tbody>
            </table>
        @empty
            <div style="text-align: center; padding: 30px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; color: #64748b;">
                No payment transactions found matching the filter criteria.
            </div>
        @endforelse

        {{-- Consolidated Grand Totals Table --}}
        @if($payments->count() > 0)
            <table class="data-table" style="margin-top: 10px;">
                <tr class="grand-total-row">
                    <td colspan="4" style="text-align: right; text-transform: uppercase; padding: 8px;">
                        CONSOLIDATED GRAND TOTALS (ALL FILTERED SCHOOLS):
                    </td>
                    <td style="width: 60px; text-align: center; padding: 8px;">{{ $grandCandidates }}</td>
                    <td style="width: 80px; text-align: right; padding: 8px;" class="font-mono">Rs. {{ number_format($grandBase, 2) }}</td>
                    <td style="width: 75px; text-align: right; padding: 8px;" class="font-mono">Rs. {{ number_format($grandFine, 2) }}</td>
                    <td style="width: 90px; text-align: right; padding: 8px;" class="font-mono">Rs. {{ number_format($grandTotal, 2) }}</td>
                    <td style="width: 65px; text-align: center; padding: 8px;">AUDITED</td>
                </tr>
            </table>
        @endif
    @endif

    {{-- Official Certification & Verification Footer --}}
    <div class="footer-note">
        <table class="footer-table">
            <tr>
                <td style="width: 70%;">
                    This is an electronically generated official financial audit statement produced by the Examination Registration Management System (ERMS).<br>
                    Data integrity validated against internal payment gateways and banking settlement records.
                </td>
                <td style="width: 30%; text-align: right;">
                    <strong>{{ $adminName ?? auth()->user()?->name ?? 'Authorized Super Administrator' }}</strong><br>
                    Board of Examinations — Finance Section
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
