@php
    if (!function_exists('staffImageToBase64')) {
        function staffImageToBase64($path)
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

    $logoPath = file_exists(public_path('logob_pdf.png'))
        ? public_path('logob_pdf.png')
        : (file_exists(public_path('logob.png'))
            ? public_path('logob.png')
            : (file_exists(public_path('logo.png')) ? public_path('logo.png') : null));
    $logoBase64 = staffImageToBase64($logoPath);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invigilators List - {{ $examination?->name ?? 'Board of Examinations' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 9.5pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* Header layout */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .header-logo {
            width: 70px;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 65px;
            max-height: 65px;
        }

        .header-title-cell {
            vertical-align: middle;
            padding-left: 10px;
        }

        .board-name {
            font-size: 8pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .report-title {
            font-size: 15pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 2px 0 0 0;
            letter-spacing: 0.5px;
        }

        .report-subtitle {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .header-meta-cell {
            vertical-align: middle;
            text-align: right;
            font-size: 8pt;
            color: #64748b;
            line-height: 1.4;
        }

        /* Stats summary cards */
        .summary-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }

        .summary-card {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 6px 10px;
            text-align: center;
        }

        .summary-card-title {
            font-size: 7.5pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .summary-card-value {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 1px;
        }

        .summary-card.logged-in {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }

        .summary-card.logged-in .summary-card-title {
            color: #166534;
        }

        .summary-card.logged-in .summary-card-value {
            color: #15803d;
        }

        .summary-card.not-logged-in {
            background-color: #fffbeb;
            border-color: #fef08a;
        }

        .summary-card.not-logged-in .summary-card-title {
            color: #854d0e;
        }

        .summary-card.not-logged-in .summary-card-value {
            color: #a16207;
        }

        /* Filter pill */
        .filter-banner {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 8pt;
            color: #1e40af;
            margin-bottom: 12px;
        }

        /* Data table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 6px;
            border: 1px solid #1e3a8a;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 6px;
            font-size: 8.5pt;
            color: #334155;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .col-sl {
            width: 4%;
            text-align: center;
        }

        .col-name {
            width: 22%;
        }

        .col-email {
            width: 24%;
        }

        .col-center {
            width: 26%;
        }

        .col-login {
            width: 14%;
            text-align: center;
        }

        .col-status {
            width: 10%;
            text-align: center;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .badge-neutral {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .sub-text {
            font-size: 7pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* Footer */
        .footer-table {
            width: 100%;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            margin-top: 20px;
            font-size: 7.5pt;
            color: #94a3b8;
        }

        .page-number:before {
            content: "Page " counter(page);
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            @if($logoBase64)
                <td class="header-logo">
                    <img src="{{ $logoBase64 }}" alt="Logo">
                </td>
            @endif
            <td class="header-title-cell">
                <div class="board-name">
                    {{ $examination?->name ? strtoupper($examination->name) : 'BOARD OF EXAMINATIONS' }}</div>
                <div class="report-title">Invigilators List</div>
                <div class="report-subtitle">Official Directory & Account Login Verification Report</div>
            </td>
            <td class="header-meta-cell">
                <strong>Generated Date:</strong><br>{{ $generatedAt }}<br>
                <strong>Total Records:</strong> {{ $summary['total'] }}
            </td>
        </tr>
    </table>

    {{-- Metrics Summary --}}
    <table class="summary-table">
        <tr>
            <td style="width: 24%; padding-right: 6px;">
                <div class="summary-card">
                    <div class="summary-card-title">Total Invigilators</div>
                    <div class="summary-card-value">{{ $summary['total'] }}</div>
                </div>
            </td>
            <td style="width: 24%; padding: 0 3px;">
                <div class="summary-card logged-in">
                    <div class="summary-card-title">Logged In</div>
                    <div class="summary-card-value">{{ $summary['logged_in'] }}</div>
                </div>
            </td>
            <td style="width: 24%; padding: 0 3px;">
                <div class="summary-card not-logged-in">
                    <div class="summary-card-title">Not Logged In</div>
                    <div class="summary-card-value">{{ $summary['not_logged_in'] }}</div>
                </div>
            </td>
            <td style="width: 28%; padding-left: 6px;">
                <div class="summary-card">
                    <div class="summary-card-title">Active / Banned</div>
                    <div class="summary-card-value" style="font-size: 11pt; padding-top: 2px;">
                        <span style="color: #16a34a;">{{ $summary['active'] }} Active</span> /
                        <span style="color: #dc2626;">{{ $summary['banned'] }} Banned</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['login_status']))
        <div class="filter-banner">
            <strong>Active Filter(s):</strong>
            @if(!empty($filters['search']))
                Search: "<em>{{ $filters['search'] }}</em>" &nbsp;|&nbsp;
            @endif
            @if(!empty($filters['status']))
                Account Status: <strong>{{ ucfirst($filters['status']) }}</strong> &nbsp;|&nbsp;
            @endif
            @if(!empty($filters['login_status']))
                Login Status:
                <strong>{{ $filters['login_status'] === 'logged_in' ? 'Logged In Only' : 'Not Logged In Only' }}</strong>
            @endif
        </div>
    @endif

    {{-- Main Invigilators Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-sl">#</th>
                <th class="col-name">Invigilator Name</th>
                <th class="col-email">Email ID</th>
                <th class="col-center">Assigned Centre</th>
                <th class="col-login">Account Logged In</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invigilators as $index => $staff)
                @php
                    $isActive = $staff->is_active ?? true;
                    $hasLoggedIn = !is_null($staff->last_login_at);
                @endphp
                <tr>
                    <td class="col-sl">{{ $index + 1 }}</td>
                    <td class="col-name">
                        <strong style="color: #0f172a;">{{ $staff->name }}</strong>
                    </td>
                    <td class="col-email">
                        <span style="color: #2563eb;">{{ $staff->email }}</span>
                    </td>
                    <td class="col-center">
                        @if($staff->school)
                            <strong>{{ $staff->school->name }}</strong>
                            <div class="sub-text">Code: {{ $staff->school->code }}</div>
                        @else
                            <span class="badge badge-neutral">Board Invigilator (All Centres)</span>
                        @endif
                    </td>
                    <td class="col-login">
                        @if($hasLoggedIn)
                            <span class="badge badge-success">Logged In</span>
                            <div class="sub-text">{{ $staff->last_login_at->format('d M Y, h:i A') }}</div>
                        @else
                            <span class="badge badge-warning">Not Logged In</span>
                            <div class="sub-text">Never accessed</div>
                        @endif
                    </td>
                    <td class="col-status">
                        @if($isActive)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Banned</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 25px; color: #64748b;">
                        No invigilator records found matching the criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <table class="footer-table">
        <tr>
            <td style="text-align: left;">
                Confidential &copy; {{ date('Y') }} {{ $examination?->name ?? 'Board of Examinations' }} — Examination
                Resource Management System
            </td>
            <td style="text-align: right;" class="page-number"></td>
        </tr>
    </table>

</body>

</html>