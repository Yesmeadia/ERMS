@php
    if (!function_exists('imageToBase64')) {
        function imageToBase64($path)
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

    $leftLogoPath = file_exists(public_path('logob.png')) ? public_path('logob.png') : (file_exists(public_path('logo.png')) ? public_path('logo.png') : null);
    $rightLogoPath = file_exists(public_path('logo.png')) ? public_path('logo.png') : null;

    $leftLogoBase64 = imageToBase64($leftLogoPath);
    $rightLogoBase64 = imageToBase64($rightLogoPath);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Exam Centre Candidate List - {{ $school->name }} ({{ $school->code }})</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20px 25px 30px 25px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .header-logos-table {
            width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .title-banner {
            background-color: #1e3a8a;
            text-align: center;
            padding: 5px 0;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .title-banner h1 {
            color: #ffffff;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }

        .header-table {
            width: 100%;
            border-bottom: 1.5px solid #3b82f6;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .venue-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .venue-meta {
            font-size: 9px;
            color: #475569;
            margin-top: 2px;
        }

        .meta-text {
            font-size: 8.5px;
            color: #64748b;
            text-align: right;
            vertical-align: top;
        }

        .summary-box {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 8.5px;
        }

        .summary-title {
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-size: 9px;
        }

        .summary-badge {
            display: inline-block;
            background-color: #e2e8f0;
            color: #334155;
            padding: 2px 5px;
            border-radius: 3px;
            margin-right: 5px;
            font-weight: bold;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .report-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            border: 1px solid #0f172a;
            padding: 5px 5px;
            text-align: left;
        }

        .report-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            color: #334155;
            vertical-align: middle;
            font-size: 8.5px;
        }

        .report-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .reg-no {
            font-family: monospace;
            font-weight: bold;
            color: #1d4ed8;
            font-size: 9px;
        }

        .ht-no {
            font-family: monospace;
            font-weight: bold;
            color: #047857;
            font-size: 8.5px;
        }

        .student-name {
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .gender-male {
            color: #2563eb;
            font-weight: bold;
        }

        .gender-female {
            color: #db2777;
            font-weight: bold;
        }

        .footer-note {
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            text-align: center;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .sig-box {
            height: 16px;
            border-bottom: 1px dotted #94a3b8;
        }
    </style>
</head>

<body>

    {{-- Top Header logos (like hall ticket) --}}
    <table class="header-logos-table">
        <tr>
            <td style="padding-right: 25px; vertical-align: middle; text-align: right; width: 50%;">
                @if($leftLogoBase64)
                    <img src="{{ $leftLogoBase64 }}" style="height: 42px; max-width: 180px;">
                @endif
            </td>
            <td style="padding-left: 25px; vertical-align: middle; text-align: left; width: 50%;">
                @if($rightLogoBase64)
                    <img src="{{ $rightLogoBase64 }}" style="height: 72px; max-width: 230px;">
                @endif
            </td>
        </tr>
    </table>

    {{-- Title Banner --}}
    <div class="title-banner">
        <h1>EXAMINATION CENTRE STUDENTS LIST</h1>
    </div>

    {{-- Venue Meta Section --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top; width: 68%;">
                <div class="venue-title">{{ $school->name }}</div>
                <div class="venue-meta">
                    <strong>Address:</strong> {{ $school->address ?: 'N/A' }} |
                    <strong>Zone:</strong> {{ $school->zone ?: 'N/A' }}, {{ $school->state ?: 'N/A' }} |
                    <strong>Session:</strong> {{ $examination?->name ?? 'All Active Sessions' }}
                </div>
            </td>
            <td class="meta-text" style="width: 32%;">
                <strong>Date Generated:</strong> {{ date('d M Y, h:i A') }}<br>
                <strong>Total Allocated:</strong> {{ $totalCount }} Candidates<br>
                <strong>School Code:</strong> <span
                    style="font-family: monospace; font-size: 10px; font-weight: bold; color: #1e3a8a;">{{ $school->code }}</span>
            </td>
        </tr>
    </table>

    {{-- Summary Box: Gender & Category Breakdown --}}
    <div class="summary-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 45%; vertical-align: top;">
                    <div class="summary-title">Gender Distribution</div>
                    <span class="summary-badge" style="background-color: #dbeafe; color: #1e40af;">Male:
                        {{ $maleCount }}</span>
                    <span class="summary-badge" style="background-color: #fce7f3; color: #9d174d;">Female:
                        {{ $femaleCount }}</span>
                    @if($otherCount > 0)
                        <span class="summary-badge" style="background-color: #fef3c7; color: #92400e;">Other:
                            {{ $otherCount }}</span>
                    @endif
                    <span class="summary-badge">Total: {{ $totalCount }}</span>
                </td>
                <td style="width: 55%; vertical-align: top;">
                    <div class="summary-title">Category Breakdown</div>
                    @foreach($categoryCounts as $catName => $count)
                        <span class="summary-badge" style="margin-bottom: 3px;">{{ $catName }}: {{ $count }}</span>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

    {{-- Main Student Table (Grouped by Category in Defined Sequence) --}}
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 3.5%; text-align: center;">#</th>
                <th style="width: 10%;">Reg. Number</th>
                <th style="width: 9%;">HT Number</th>
                <th style="width: 22%;">Candidate Name</th>
                <th style="width: 6%; text-align: center;">Gender</th>
                <th style="width: 12%;">Category</th>
                <th style="width: 8%;">Class</th>
                <th style="width: 17.5%;">Origin School</th>
                <th style="width: 12%; text-align: center;">Candidate Signature</th>
            </tr>
        </thead>
        <tbody>
            @php $globalIndex = 0; @endphp
            @forelse($groupedStudents as $categoryName => $catStudents)
                <tr style="background-color: #0f172a;">
                    <td colspan="9"
                        style="background-color: #0f172a; color: #ffffff; font-weight: bold; padding: 5px 8px; font-size: 9px; letter-spacing: 0.5px; border: 1px solid #0f172a;">
                        CATEGORY: {{ strtoupper($categoryName) }} &nbsp;·&nbsp; <span
                            style="color: #93c5fd; font-weight: normal;">{{ count($catStudents) }} Candidates</span>
                    </td>
                </tr>
                @foreach($catStudents as $student)
                    @php $globalIndex++; @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold; color: #64748b;">
                            {{ $globalIndex }}
                        </td>
                        <td class="reg-no">
                            {{ $student->registration_number ?: 'PENDING' }}
                        </td>
                        <td class="ht-no">
                            {{ $student->hall_ticket_number ?: 'NOT ISSUED' }}
                        </td>
                        <td>
                            <div class="student-name">{{ $student->name }}</div>
                        </td>
                        <td style="text-align: center;">
                            @php
                                $gClean = strtolower(trim($student->gender ?? ''));
                            @endphp
                            @if(in_array($gClean, ['male', 'm']))
                                <span class="gender-male">M</span>
                            @elseif(in_array($gClean, ['female', 'f']))
                                <span class="gender-female">F</span>
                            @else
                                <span>{{ strtoupper(substr($student->gender ?? 'O', 0, 1)) }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $student->category?->name ?? 'General' }}</strong>
                        </td>
                        <td>
                            {{ $student->class?->name ?? 'N/A' }}
                        </td>
                        <td>
                            {{ $student->school?->name ?? 'N/A' }}
                        </td>
                        <td style="text-align: center;">
                            <div class="sig-box"></div>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 25px; color: #94a3b8; font-style: italic;">
                        No candidates allocated to this examination venue under the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer Info --}}
    <div class="footer-note">
        * Examination Centre Official Candidate List · Generated via ERMS · {{ $school->name }}
    </div>

</body>

</html>