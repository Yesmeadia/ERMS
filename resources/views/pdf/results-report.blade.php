@php
    $leftLogoBase64 = $leftLogoBase64 ?? null;
    $rightLogoBase64 = $rightLogoBase64 ?? null;
    $signBase64 = $signBase64 ?? null;

    $examTitle = $examTitle ?? ($examName ?? ($filters['examination'] ?? 'YES GENIUS TALENT SEARCH'));
    $subtitle = $subtitle ?? 'Candidate Examination Result Statement & Tabulation Register';
    $title = $title ?? 'STATEMENT OF RESULTS';
    $isSuperAdmin = $isSuperAdmin ?? false;
    $generatedAt = $generatedAt ?? date('d M Y, h:i A');
    $partInfo = $partInfo ?? null;

    if (
        !isset($filters['examination']) ||
        $filters['examination'] === 'All Examination Sessions' ||
        $filters['examination'] === 'All Published Sessions'
    ) {
        $filters['examination'] = $examTitle;
    }

    $pages = $pages ?? [[]];
    $totalPages = count($pages);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Examination Results Statement' }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 7pt;
            line-height: 1.15;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .page-wrapper {
            page-break-after: always;
        }

        .page-wrapper:last-child {
            page-break-after: avoid;
        }

        .header-logos-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2px;
        }

        .header-logo-left {
            width: 15%;
            text-align: left;
            vertical-align: middle;
        }

        .header-logo-center {
            width: 70%;
            text-align: center;
            vertical-align: middle;
        }

        .header-logo-right {
            width: 15%;
            text-align: right;
            vertical-align: middle;
        }

        .header-logo {
            max-height: 32px;
            max-width: 80px;
        }

        .exam-title {
            font-size: 11pt;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .exam-subtitle {
            font-size: 7pt;
            color: #475569;
        }

        .title-banner {
            background-color: #1e3a8a;
            text-align: center;
            padding: 3px 0;
            margin-bottom: 2px;
            border-radius: 2px;
        }

        .title-banner h1 {
            color: #ffffff;
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .meta-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2px;
            background-color: #f8fafc;
            border: 0.5pt solid #cbd5e1;
            border-radius: 2px;
        }

        .meta-table td {
            padding: 2px 4px;
            font-size: 6.5pt;
            color: #334155;
            vertical-align: middle;
        }

        .meta-label {
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            font-size: 6pt;
        }

        .stats-summary {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 3px;
        }

        .stats-summary td {
            padding: 2px 4px;
            text-align: center;
            border: 0.5pt solid #cbd5e1;
            background-color: #f1f5f9;
        }

        .stats-val {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            display: block;
        }

        .stats-lbl {
            font-size: 6pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .continuation-bar {
            border-bottom: 1pt solid #1e3a8a;
            padding-bottom: 1px;
            margin-bottom: 3px;
        }

        .continuation-bar table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
        }

        .continuation-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }

        .continuation-meta {
            font-size: 6.5pt;
            color: #64748b;
            text-align: right;
        }

        table.results-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 0.5pt solid #cbd5e1;
            margin-bottom: 3px;
        }

        table.results-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 6pt;
            text-transform: uppercase;
            letter-spacing: 0.1px;
            border: 0.5pt solid #334155;
            padding: 2.5px 1px;
            text-align: left;
            overflow: hidden;
        }

        table.results-table td {
            border: 0.5pt solid #e2e8f0;
            padding: 2px 2px;
            color: #1e293b;
            vertical-align: middle;
            font-size: 6.5pt;
            overflow: hidden;
        }

        table.results-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .c-center {
            text-align: center;
        }

        .c-bold {
            font-weight: bold;
        }

        .c-reg {
            color: #1e40af;
            font-weight: bold;
        }

        .badge-pass {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            font-weight: bold;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 6pt;
            white-space: nowrap;
        }

        .badge-fail {
            display: inline-block;
            background-color: #ffe4e6;
            color: #b91c1c;
            font-weight: bold;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 6pt;
            white-space: nowrap;
        }

        .badge-absent {
            display: inline-block;
            background-color: #ffedd5;
            color: #c2410c;
            font-weight: bold;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 6pt;
            white-space: nowrap;
        }

        .badge-withheld {
            display: inline-block;
            background-color: #fef3c7;
            color: #b45309;
            font-weight: bold;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 6pt;
            white-space: nowrap;
        }

        .page-footer-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2px;
        }

        .page-footer-table td {
            font-size: 6pt;
            color: #64748b;
            vertical-align: bottom;
        }

        .sign-box {
            text-align: right;
            font-size: 6.5pt;
            color: #1e293b;
        }

        .sign-title {
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            border-top: 0.5pt solid #94a3b8;
            padding-top: 2px;
            display: inline-block;
            min-width: 130px;
            text-align: center;
        }
    </style>
</head>

<body>

    @foreach($pages as $pageIndex => $pageRows)
        @php
            $currentPage = $pageIndex + 1;
            $isFirstPage = ($pageIndex === 0);
            $isLastPage = ($pageIndex === $totalPages - 1);
        @endphp

        <div class="page-wrapper">

            @if($isFirstPage)
                {{-- Logos and Organization Header --}}
                <table class="header-logos-table">
                    <tr>
                        <td class="header-logo-left">
                            @if ($leftLogoBase64)
                                <img src="{{ $leftLogoBase64 }}" class="header-logo" alt="Logo Left">
                            @endif
                        </td>

                        <td class="header-logo-center">
                            <div class="exam-title">
                                {{ $examTitle }}
                            </div>
                            <div class="exam-subtitle">
                                {{ $subtitle }}
                                @if($partInfo)
                                    &bull;
                                    <strong style="color: #1e3a8a;">{{ $partInfo }}</strong>
                                @endif
                            </div>
                        </td>

                        <td class="header-logo-right">
                            @if ($rightLogoBase64)
                                <img src="{{ $rightLogoBase64 }}" class="header-logo" alt="Logo Right">
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- Title Banner --}}
                <div class="title-banner">
                    <h1>{{ $title }}</h1>
                </div>

                {{-- Metadata & Applied Filter Summary --}}
                <table class="meta-table">
                    <tr>
                        <td style="width: 25%;">
                            <span class="meta-label">Examination:</span>
                            <strong>{{ $filters['examination'] ?? 'All Sessions' }}</strong>
                        </td>
                        <td style="width: 25%;">
                            <span class="meta-label">School:</span>
                            <strong>{{ $filters['school'] ?? 'All Schools' }}</strong>
                        </td>
                        <td style="width: 25%;">
                            <span class="meta-label">Zone:</span>
                            <strong>{{ $filters['zone'] ?? 'All Zones' }}</strong>
                        </td>
                        <td style="width: 25%; text-align: right;">
                            <span class="meta-label">Generated:</span>
                            <strong>{{ $generatedAt }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="meta-label">Category:</span>
                            <strong>{{ $filters['category'] ?? 'All Categories' }}</strong>
                        </td>
                        <td>
                            <span class="meta-label">Class:</span>
                            <strong>{{ $filters['class'] ?? 'All Classes' }}</strong>
                        </td>
                        <td>
                            <span class="meta-label">Gender:</span>
                            <strong>{{ $filters['gender'] ?? 'All' }}</strong>
                        </td>
                        <td style="text-align: right;">
                            <span class="meta-label">Result Filter:</span>
                            <strong>{{ $filters['result_status'] ?? 'All Candidates' }}</strong>
                        </td>
                    </tr>
                </table>

                {{-- Performance Summary KPI Cards --}}
                <table class="stats-summary">
                    <tr>
                        <td>
                            <span class="stats-val">{{ number_format($stats['total'] ?? 0) }}</span>
                            <span class="stats-lbl">Total Candidates</span>
                        </td>
                        <td>
                            <span class="stats-val">{{ number_format($stats['appeared'] ?? 0) }}</span>
                            <span class="stats-lbl">Appeared</span>
                        </td>
                        <td style="background-color: #f0fdf4;">
                            <span class="stats-val" style="color: #16a34a;">{{ number_format($stats['passed'] ?? 0) }}</span>
                            <span class="stats-lbl" style="color: #15803d;">Passed</span>
                        </td>
                        <td style="background-color: #fef2f2;">
                            <span class="stats-val" style="color: #dc2626;">{{ number_format($stats['failed'] ?? 0) }}</span>
                            <span class="stats-lbl" style="color: #b91c1c;">Failed</span>
                        </td>
                        <td style="background-color: #fff7ed;">
                            <span class="stats-val" style="color: #ea580c;">{{ number_format($stats['absent'] ?? 0) }}</span>
                            <span class="stats-lbl" style="color: #c2410c;">Absent</span>
                        </td>
                        <td style="background-color: #eff6ff;">
                            <span class="stats-val" style="color: #2563eb;">{{ $stats['pass_rate'] ?? 0 }}%</span>
                            <span class="stats-lbl" style="color: #1d4ed8;">Pass Rate</span>
                        </td>
                    </tr>
                </table>
            @endif

            {{-- Results Table with Strict Colgroup Percentages --}}
            <table class="results-table">
                <colgroup>
                    @if($isSuperAdmin)
                        {{-- Super Admin: 14 Columns (Total = 100%) --}}
                        <col style="width: 2.5%;"> {{-- # --}}
                        <col style="width: 5.0%;"> {{-- REG. NO --}}
                        <col style="width: 8.5%;"> {{-- HT NO --}}
                        <col style="width: 25.0%;"> {{-- STUDENT NAME --}}
                        <col style="width: 14.0%;"> {{-- SCHOOL --}}
                        <col style="width: 8.0%;"> {{-- STATE --}}
                        <col style="width: 6.0%;"> {{-- ZONE --}}
                        <col style="width: 4.5%;"> {{-- CLASS --}}
                        <col style="width: 8.0%;"> {{-- CATEGORY --}}
                        <col style="width: 4.5%;"> {{-- GEN --}}
                        <col style="width: 4.5%;"> {{-- MARKS --}}
                        <col style="width: 3.5%;"> {{-- % --}}
                        <col style="width: 3.5%;"> {{-- GRD --}}
                        <col style="width: 6.5%;"> {{-- STATUS --}}
                    @else
                        {{-- School Admin: 11 Columns (Total = 100%) --}}
                        <col style="width: 3.0%;"> {{-- # --}}
                        <col style="width: 7.0%;"> {{-- REG. NO --}}
                        <col style="width: 11.0%;"> {{-- HT NO --}}
                        <col style="width: 38.0%;"> {{-- STUDENT NAME --}}
                        <col style="width: 5.5%;"> {{-- CLASS --}}
                        <col style="width: 10.5%;"> {{-- CATEGORY --}}
                        <col style="width: 5.0%;"> {{-- GEN --}}
                        <col style="width: 6.0%;"> {{-- MARKS --}}
                        <col style="width: 4.0%;"> {{-- % --}}
                        <col style="width: 3.5%;"> {{-- GRD --}}
                        <col style="width: 6.5%;"> {{-- STATUS --}}
                    @endif
                </colgroup>

                <thead>
                    <tr>
                        @if($isSuperAdmin)
                            <th style="width: 2.5%; text-align: center;">#</th>
                            <th style="width: 5.0%;">Reg. No</th>
                            <th style="width: 8.5%;">HT No</th>
                            <th style="width: 25.0%;">Student Name</th>
                            <th style="width: 14.0%;">School</th>
                            <th style="width: 8.0%;">State</th>
                            <th style="width: 6.0%;">Zone</th>
                            <th style="width: 4.5%;">Class</th>
                            <th style="width: 8.0%;">Category</th>
                            <th style="width: 4.5%; text-align: center;">Gen</th>
                            <th style="width: 4.5%; text-align: center;">Marks</th>
                            <th style="width: 3.5%; text-align: center;">%</th>
                            <th style="width: 3.5%; text-align: center;">Grd</th>
                            <th style="width: 6.5%; text-align: center;">Status</th>
                        @else
                            <th style="width: 3.0%; text-align: center;">#</th>
                            <th style="width: 7.0%;">Reg. No</th>
                            <th style="width: 11.0%;">HT No</th>
                            <th style="width: 38.0%;">Student Name</th>
                            <th style="width: 5.5%;">Class</th>
                            <th style="width: 10.5%;">Category</th>
                            <th style="width: 5.0%; text-align: center;">Gen</th>
                            <th style="width: 6.0%; text-align: center;">Marks</th>
                            <th style="width: 4.0%; text-align: center;">%</th>
                            <th style="width: 3.5%; text-align: center;">Grd</th>
                            <th style="width: 6.5%; text-align: center;">Status</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($pageRows as $row)
                        <tr>
                            <td class="c-center">{{ $row['sno'] }}</td>
                            <td class="c-reg">{{ $row['reg_no'] }}</td>
                            <td>{{ $row['ht_no'] }}</td>
                            <td class="c-bold">{{ $row['name'] }}</td>
                            @if($isSuperAdmin)
                                <td>{{ $row['school'] }}</td>
                                <td>{{ $row['state'] }}</td>
                                <td>{{ $row['zone'] }}</td>
                            @endif
                            <td>{{ $row['class'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td class="c-center">{{ $row['gender'] }}</td>
                            <td class="c-center c-bold">{{ $row['marks'] }}</td>
                            <td class="c-center">{{ $row['pct'] }}</td>
                            <td class="c-center c-bold">{{ $row['grade'] }}</td>
                            <td class="c-center">
                                <span class="{{ $row['status_class'] }}">{{ $row['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSuperAdmin ? 14 : 11 }}"
                                style="text-align: center; padding: 10px; color: #94a3b8; font-style: italic;">
                                No student result records found matching the specified filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Page Footer --}}
            <table class="page-footer-table">
                <tr>
                    <td style="width: 55%;">
                        <strong>Confidential Tabulation Register</strong> | Generated: {{ $generatedAt }} | Page
                        {{ $currentPage }} of {{ $totalPages }}
                    </td>

                    <td style="width: 45%; text-align: right;">
                        @if($isLastPage)
                            <div class="sign-box">
                                @if ($signBase64)
                                    <div style="margin-bottom: 2px;">
                                        <img src="{{ $signBase64 }}" style="max-height: 32px; max-width: 120px;"
                                            alt="Controller Signature">
                                    </div>
                                @endif
                                <div class="sign-title">Controller of Examinations</div>
                            </div>
                        @else
                            <span style="font-style: italic; color: #94a3b8;">(Continued on next page...)</span>
                        @endif
                    </td>
                </tr>
            </table>

        </div>
    @endforeach

</body>

</html>