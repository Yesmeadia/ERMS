<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Examination Merit List: {{ $exam->name }}</title>
    <style>
        @page { margin: 20px 25px; }
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; color: #0f172a; }
        .header p { margin: 2px 0; font-size: 10px; color: #475569; }
        .meta-table { width: 100%; margin-bottom: 10px; font-size: 10px; }
        .meta-table td { padding: 2px 4px; }
        table.results { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.results th, table.results td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; }
        table.results th { background-color: #f1f5f9; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .pass { color: #059669; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 15px; font-size: 9px; text-align: right; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Yasin Education Services India Foundation</h1>
        <p>Examination Management System (ERMS) &bull; Official Merit List</p>
        <p><strong>Examination:</strong> {{ $exam->name }} ({{ $exam->code }}) &bull; <strong>Category:</strong> {{ $exam->category->name ?? 'General' }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Exam Date:</strong> {{ $exam->exam_date ? $exam->exam_date->format('d M Y') : 'N/A' }}</td>
            <td><strong>Total Marks:</strong> {{ $exam->total_marks }}</td>
            <td><strong>Passing Marks:</strong> {{ $exam->pass_marks }}</td>
            <td><strong>Total Candidates:</strong> {{ $results->count() }}</td>
            <td class="text-right"><strong>Generated At:</strong> {{ now()->format('d M Y, H:i') }}</td>
        </tr>
    </table>

    <table class="results">
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">Rank</th>
                <th style="width: 70px;">Reg No</th>
                <th>Candidate Name</th>
                <th>Institution / School</th>
                <th class="text-center" style="width: 50px;">Attempted</th>
                <th class="text-center" style="width: 45px;">Correct</th>
                <th class="text-right" style="width: 45px;">Score</th>
                <th class="text-center" style="width: 40px;">%</th>
                <th class="text-center" style="width: 40px;">Grade</th>
                <th class="text-center" style="width: 45px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $res)
            <tr>
                <td class="text-center font-bold">{{ $res->rank ? '#' . $res->rank : '-' }}</td>
                <td class="font-bold">{{ $res->student->registration_number }}</td>
                <td>{{ $res->student->name }}</td>
                <td>{{ $res->student->school->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $res->attempted_questions_count }} / {{ $res->total_questions }}</td>
                <td class="text-center">{{ $res->correct_answers_count }}</td>
                <td class="text-right font-bold">{{ number_format($res->final_score, 2) }}</td>
                <td class="text-center">{{ number_format($res->percentage, 1) }}%</td>
                <td class="text-center font-bold">{{ $res->grade ?: '-' }}</td>
                <td class="text-center">
                    <span class="{{ $res->is_passed ? 'pass' : 'fail' }}">
                        {{ $res->is_passed ? 'PASS' : 'FAIL' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 15px;">No candidate results available for this examination.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Confidential Document &bull; ERMS Online Examination System &bull; Page 1 of 1
    </div>
</body>
</html>
