<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Seat Planner (33 Per Page) - {{ $school->name }} ({{ $school->code }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 4mm 4mm 4mm 4mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .page-break {
            page-break-after: always;
        }

        .slips-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }

        .slip-cell {
            width: 33.333%;
            vertical-align: top;
            text-align: center;
            padding: 0.8mm 1mm;
            box-sizing: border-box;
        }

        .slip-box {
            border: 1.5px solid #1e3a8a;
            border-radius: 4px;
            padding: 1.8mm 1.5mm;
            text-align: center;
            background-color: #ffffff;
            box-sizing: border-box;
        }

        .category-heading {
            font-size: 9px;
            font-weight: 800;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            line-height: 1;
            margin-bottom: 1.5px;
            text-align: center;
        }

        .reg-no-val {
            font-size: 24px;
            font-weight: 900;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1e3a8a;
            letter-spacing: 0.8px;
            line-height: 1.05;
            margin-bottom: 1.5px;
            text-align: center;
        }

        .name-val {
            font-size: 15px;
            font-weight: 800;
            color: #000000;
            text-transform: uppercase;
            line-height: 1.1;
            margin-bottom: 1.5px;
            max-height: 2.2em;
            overflow: hidden;
            text-align: center;
        }

        .school-val {
            font-size: 10px;
            font-weight: 600;
            color: #334155;
            line-height: 1.1;
            max-height: 2.2em;
            overflow: hidden;
            text-align: center;
        }
    </style>
</head>

<body>

    @forelse($studentChunks as $chunkIndex => $chunk)
        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            <table class="slips-table">
                @php
                    $chunkStudents = $chunk->values();
                @endphp
                @for($row = 0; $row < 11; $row++)
                    <tr>
                        @for($col = 0; $col < 3; $col++)
                            @php
                                $slipIndex = ($row * 3) + $col;
                                $student = $chunkStudents->get($slipIndex);
                            @endphp
                            <td class="slip-cell">
                                @if($student)
                                    <div class="slip-box">
                                        {{-- Category Heading (Centered) --}}
                                        <div class="category-heading">
                                            CATEGORY: {{ strtoupper($student->category?->name ?? 'GENERAL') }}
                                        </div>

                                        {{-- Register Number (Enlarged & Centered) --}}
                                        <div class="reg-no-val">
                                            {{ $student->registration_number ?: 'NOT ISSUED' }}
                                        </div>

                                        {{-- Candidate Name (Enlarged & Centered) --}}
                                        <div class="name-val">
                                            {{ $student->name }}
                                        </div>

                                        {{-- School Name (Centered) --}}
                                        <div class="school-val">
                                            {{ $student->school?->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 50px 20px; font-family: sans-serif; color: #64748b;">
            <h2 style="color: #1e3a8a; margin-bottom: 5px;">{{ $school->name }} ({{ $school->code }})</h2>
            <p style="font-size: 13px; color: #64748b;">Seat Planner Desk Slips (33 Per Page)</p>
            <div
                style="margin-top: 30px; padding: 20px; border: 1.5px dashed #cbd5e1; border-radius: 8px; font-size: 12px; color: #94a3b8;">
                No candidates currently assigned to this examination venue under the selected criteria.
            </div>
        </div>
    @endforelse

</body>

</html>