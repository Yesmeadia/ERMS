<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 35px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-text {
            font-size: 9px;
            color: #64748b;
            text-align: right;
            vertical-align: bottom;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
            text-align: left;
        }
        .report-table td {
            border: 1px solid #e2e8f0;
            padding: 7px 6px;
            color: #334155;
            vertical-align: top;
        }
        .report-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .summary-info {
            font-size: 10px;
            color: #64748b;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    {{-- Report Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div style="font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">BOARD OF EXAMINATIONS</div>
                <div class="title">{{ $title }}</div>
            </td>
            <td class="meta-text">
                Generated: {{ date('d M Y, h:i A') }}<br>
                Total Records: {{ count($reportData['rows']) }}
            </td>
        </tr>
    </table>

    {{-- Report Data Table --}}
    <table class="report-table">
        <thead>
            <tr>
                @foreach($reportData['headings'] as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['rows'] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($reportData['headings']) }}" style="text-align: center; padding: 30px; color: #94a3b8; font-style: italic;">
                        No data available for this report criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary / Footer info --}}
    <div class="summary-info">
        * This is a system-generated document from the Examination Registration Management System (ERMS).
    </div>

</body>
</html>
