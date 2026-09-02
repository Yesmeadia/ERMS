@php
    if (!function_exists('generateBarcodeSVG')) {
        function generateBarcodeSVG($text)
        {
            $patterns = [
                '0' => '101001101101',
                '1' => '110100101011',
                '2' => '101100101011',
                '3' => '110110010101',
                '4' => '101001101011',
                '5' => '110100110101',
                '6' => '101100110101',
                '7' => '101001011011',
                '8' => '110100101101',
                '9' => '101100101101',
                'A' => '110101001011',
                'B' => '101101001011',
                'C' => '110110100101',
                'D' => '101011001011',
                'E' => '110101100101',
                'F' => '101101100101',
                'G' => '101010011011',
                'H' => '110101001101',
                'I' => '101101001101',
                'J' => '101011001101',
                'K' => '110101010011',
                'L' => '101101010011',
                'M' => '110110101001',
                'N' => '101011010011',
                'O' => '110101101001',
                'P' => '101101101001',
                'Q' => '101010110011',
                'R' => '110101011001',
                'S' => '101101011001',
                'T' => '101011011001',
                'U' => '110010101011',
                'V' => '100110101011',
                'W' => '110011010101',
                'X' => '100101101011',
                'Y' => '110010110101',
                'Z' => '100110110101',
                '-' => '100101011011',
                '.' => '110010101101',
                ' ' => '100110101101',
                '*' => '100101101101',
                '$' => '100100100101',
                '/' => '100100101001',
                '+' => '100101001001',
                '%' => '100100100101'
            ];

            $text = strtoupper((string) $text);
            $sanitized = '';
            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];
                $sanitized .= isset($patterns[$char]) ? $char : ' ';
            }

            $formattedText = '*' . $sanitized . '*';
            $charWidth = 12;
            $gapWidth = 1;
            $len = strlen($formattedText);
            $totalUnits = $len * $charWidth + ($len - 1) * $gapWidth;

            $barHeight = 28;
            $scale = 1.2;
            $svgWidth = $totalUnits * $scale;
            $svgHeight = $barHeight + 10;

            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="' . $svgHeight . '" viewBox="0 0 ' . $svgWidth . ' ' . $svgHeight . '">';
            $svg .= '<rect width="100%" height="100%" fill="#ffffff" />';

            $x = 0;
            for ($i = 0; $i < $len; $i++) {
                $char = $formattedText[$i];
                if (!isset($patterns[$char]))
                    continue;
                $pattern = $patterns[$char];

                for ($j = 0; $j < strlen($pattern); $j++) {
                    if ($pattern[$j] == '1') {
                        $svg .= '<rect x="' . ($x * $scale) . '" y="0" width="' . $scale . '" height="' . $barHeight . '" fill="#000000" />';
                    }
                    $x++;
                }
                if ($i < $len - 1)
                    $x += $gapWidth;
            }

            $svg .= '<text x="' . ($svgWidth / 2) . '" y="' . ($barHeight + 8) . '" font-family="Metropolis, sans-serif" font-size="7" fill="#000000" text-anchor="middle" letter-spacing="1.5">' . $text . '</text>';
            $svg .= '</svg>';

            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
    }

    // Category-wise banner color (matches Hall Ticket standard)
    $categoryName = strtoupper(trim($student->category->name ?? ''));
    if (str_starts_with($categoryName, 'RAINBOW')) {
        $bannerColor = '#2e7d32'; // RAINBOW → Green
    } elseif (str_starts_with($categoryName, 'PLANET')) {
        $bannerColor = '#c62828'; // PLANET → Red
    } elseif (str_starts_with($categoryName, 'GALAXY')) {
        $bannerColor = '#1565c0'; // GALAXY → Blue
    } else {
        $bannerColor = '#3e6b27'; // Default Green
    }

    $barcodeText = $student->hall_ticket_number ?: $student->registration_number;
    $barcodeSvg = generateBarcodeSVG($barcodeText);

    $signPath = file_exists(public_path('Sign.png')) ? asset('Sign.png') : (file_exists(public_path('sign.png')) ? asset('sign.png') : null);

    // Photo matching Hall Ticket resolution
    $photoSrc = null;
    if ($student->photograph && file_exists(public_path('storage/' . $student->photograph))) {
        $photoSrc = asset('storage/' . $student->photograph);
    } elseif ($student->photograph && file_exists(storage_path('app/public/' . $student->photograph))) {
        $photoSrc = asset('storage/' . $student->photograph);
    } elseif ($student->photo_url && !str_starts_with($student->photo_url, 'data:image/svg')) {
        $photoSrc = $student->photo_url;
    }
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Statement of Marks - {{ $student->name }} | ERMS</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Courier+Prime:wght@400;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        /* Certificate / Statement Sheet (Full Desktop Dimensions) */
        .marksheet-sheet {
            background-color: #ffffff;
            color: #000000;
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            padding: 20px 25px;
            box-sizing: border-box;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        /* Table styles identical to Hall Ticket */
        .details-grid,
        .scores-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .details-grid td,
        .scores-grid td,
        .scores-grid th {
            border: 1px solid #000000;
            padding: 7px 10px;
            font-size: 12px;
            color: #000000;
            vertical-align: middle;
        }

        .label-cell {
            font-weight: bold;
            width: 32%;
            background-color: #ffffff;
        }

        .value-cell {
            width: 68%;
        }

        .reg-no-label {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 1px;
            margin-top: -2px;
            text-align: left;
            max-width: 135px;
            margin-left: auto;
            color: #000000;
        }

        .reg-no-box {
            border: 1px solid #000000;
            padding: 0 1px;
            text-align: center;
            font-family: 'Outfit', monospace;
            font-size: 22px;
            font-weight: bold;
            color: #d32f2f;
            background-color: #ffffff;
            margin-bottom: 4px;
            margin-top: -1px;
            height: 30px;
            line-height: 30px;
            width: 100%;
            max-width: 135px;
            margin-left: auto;
            display: block;
            box-sizing: border-box;
        }

        .photo-box {
            border: 1px solid #000000;
            width: 100%;
            max-width: 135px;
            height: 170px;
            /* standard passport size: 35mm x 45mm */
            background-color: #ffffff;
            position: relative;
            text-align: center;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-left: auto;
        }

        .photo-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: 100%;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .photo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #94a3b8;
            font-weight: bold;
            border: 1px dashed #cbd5e1;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            background-color: #f8fafc;
            text-align: center;
            line-height: 1.4;
        }

        /* Banner matching Hall Ticket (Category-wise Background Color) */
        .title-banner {
            background-color:
                {{ $bannerColor }}
                !important;
            text-align: center;
            padding: 6px 0;
            margin-bottom: 22px;
            margin-left: -25px;
            margin-right: -25px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .title-banner h1 {
            color: #ffffff !important;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin: 0;
            text-transform: uppercase;
            font-family: 'Outfit', Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Header Logo Section */
        .header-logo-table {
            margin: 0 auto;
            border-collapse: collapse;
            margin-bottom: 12px;
            width: auto;
        }

        .header-logo-left {
            padding-right: 40px;
            vertical-align: middle;
            text-align: right;
        }

        .header-logo-right {
            padding-left: 40px;
            vertical-align: middle;
            text-align: left;
        }

        .logo-img-left {
            height: 52px;
            max-width: 220px;
            object-fit: contain;
            display: inline-block;
        }

        .logo-img-right {
            height: 98px;
            max-width: 280px;
            object-fit: contain;
            display: inline-block;
        }

        /* Candidate & Scores Sections */
        .candidate-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .details-col {
            width: 76%;
            vertical-align: top;
            padding-right: 15px;
        }

        .photo-col {
            width: 24%;
            vertical-align: top;
            text-align: right;
        }

        .scores-table-wrap {
            margin-top: 8px;
            margin-bottom: 12px;
            width: 100%;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary-left {
            width: 50%;
            vertical-align: middle;
        }

        .summary-right {
            width: 50%;
            vertical-align: middle;
            text-align: right;
        }

        .barcode-sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 6px;
        }

        .barcode-col {
            width: 50%;
            vertical-align: middle;
            text-align: left;
        }

        .signature-col {
            width: 50%;
            vertical-align: middle;
            text-align: right;
        }

        .barcode-svg-img {
            height: 38px;
            width: auto;
            max-width: 100%;
            display: block;
        }

        .sign-img {
            height: 56px;
            width: auto;
            max-height: 56px;
            display: block;
            margin: 0 auto 2px auto;
        }

        .controller-label {
            font-weight: bold;
            font-size: 12px;
            color: #000000;
            margin-top: 2px;
        }

        /* ─── MOBILE VIEW: PROPORTIONALLY SCALED SMALL SIZE (Same Design, Scaled Down) ─── */
        @media (max-width: 640px) {
            .marksheet-sheet {
                padding: 12px 14px;
                border-radius: 8px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
            }

            .action-bar {
                flex-direction: column !important;
                width: 100% !important;
                gap: 8px !important;
            }

            .action-bar a,
            .action-bar button {
                width: 100% !important;
                justify-content: center !important;
                text-align: center !important;
            }

            /* Header Logos */
            .header-logo-table {
                margin-bottom: 8px;
            }

            .header-logo-left {
                padding-right: 14px;
            }

            .header-logo-right {
                padding-left: 14px;
            }

            .logo-img-left {
                height: 32px;
                max-width: 110px;
            }

            .logo-img-right {
                height: 52px;
                max-width: 140px;
            }

            /* Banner */
            .title-banner {
                margin-left: -14px;
                margin-right: -14px;
                margin-bottom: 10px;
                padding: 3.5px 0;
            }

            .title-banner h1 {
                font-size: 12.5px;
                letter-spacing: 0.8px;
            }

            /* Candidate Layout (Maintains exact 2-column side-by-side design) */
            .candidate-table {
                display: table !important;
                width: 100% !important;
                margin-bottom: 6px;
            }

            .details-col {
                display: table-cell !important;
                width: 73% !important;
                padding-right: 6px !important;
                vertical-align: top !important;
            }

            .photo-col {
                display: table-cell !important;
                width: 27% !important;
                text-align: right !important;
                vertical-align: top !important;
            }

            .details-grid td {
                padding: 3px 5px !important;
                font-size: 9px !important;
                line-height: 1.25 !important;
            }

            .label-cell {
                width: 35% !important;
                font-size: 8.5px !important;
            }

            .reg-no-label {
                font-size: 8px !important;
                margin-bottom: 1px !important;
                max-width: 100% !important;
            }

            .reg-no-box {
                font-size: 13px !important;
                height: 19px !important;
                line-height: 19px !important;
                width: 100% !important;
                max-width: 100% !important;
                margin-bottom: 2px !important;
            }

            .photo-box {
                height: 105px !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .photo-img {
                height: 105px !important;
                max-height: 105px !important;
            }

            .photo-placeholder {
                font-size: 7.5px !important;
            }

            /* Scores Grid */
            .scores-table-wrap {
                margin-top: 6px;
                margin-bottom: 8px;
            }

            .scores-grid td,
            .scores-grid th {
                padding: 3px 5px !important;
                font-size: 9px !important;
                line-height: 1.2 !important;
            }

            /* Summary & Status */
            .summary-table {
                display: table !important;
                width: 100% !important;
                margin-bottom: 8px;
            }

            .summary-left {
                display: table-cell !important;
                width: 62% !important;
                font-size: 9px !important;
                line-height: 1.3 !important;
            }

            .summary-right {
                display: table-cell !important;
                width: 38% !important;
                text-align: right !important;
            }

            .status-badge-box {
                padding: 4px 12px !important;
                font-size: 11px !important;
                letter-spacing: 1px !important;
                border-radius: 6px !important;
            }

            /* Barcode & Signature */
            .barcode-sig-table {
                display: table !important;
                width: 100% !important;
                margin-top: 6px;
                margin-bottom: 4px;
            }

            .barcode-col {
                display: table-cell !important;
                width: 50% !important;
                vertical-align: middle !important;
            }

            .signature-col {
                display: table-cell !important;
                width: 50% !important;
                vertical-align: middle !important;
                text-align: right !important;
            }

            .barcode-svg-img {
                height: 24px !important;
            }

            .sign-img {
                height: 32px !important;
                max-height: 32px !important;
            }

            .controller-label {
                font-size: 8.5px !important;
            }

            .footnote-table {
                font-size: 7.5px !important;
            }
        }

        /* ─── PRINT MEDIA STYLES (A4 Layout) ─── */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print,
            .footer,
            footer {
                display: none !important;
            }

            .marksheet-sheet {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 15px 20px !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .title-banner {
                background-color:
                    {{ $bannerColor }}
                    !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin-left: -20px !important;
                margin-right: -20px !important;
                margin-bottom: 22px !important;
                padding: 6px 0 !important;
            }

            .title-banner h1 {
                color: #ffffff !important;
                font-size: 22px !important;
                letter-spacing: 1.5px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .candidate-table {
                display: table !important;
                width: 100% !important;
            }

            .details-col {
                display: table-cell !important;
                width: 76% !important;
                padding-right: 15px !important;
            }

            .photo-col {
                display: table-cell !important;
                width: 24% !important;
                text-align: right !important;
            }

            .summary-table {
                display: table !important;
                width: 100% !important;
            }

            .summary-left {
                display: table-cell !important;
                width: 50% !important;
                text-align: left !important;
            }

            .summary-right {
                display: table-cell !important;
                width: 50% !important;
                text-align: right !important;
            }

            @page {
                size: A4;
                margin: 8mm;
            }
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between relative overflow-x-hidden">

    <!-- Glowing Background Ambient Orbs (Hidden on print) -->
    <div
        class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none no-print">
    </div>
    <div
        class="absolute -bottom-40 -left-40 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl pointer-events-none no-print">
    </div>

    <div class="w-full max-w-4xl mx-auto px-2 sm:px-4 py-4 sm:py-8 flex-1 flex flex-col items-center justify-center">

        <!-- Main Marksheet Sheet (Scaled Down on Mobile) -->
        <div class="marksheet-sheet">

            <!-- 1. Header Section: Logos -->
            <table class="header-logo-table">
                <tr>
                    <td class="header-logo-left">
                        @if(file_exists(public_path('logob.png')))
                            <img src="{{ asset('logob.png') }}" class="logo-img-left">
                        @elseif(file_exists(public_path('logo.png')))
                            <img src="{{ asset('logo.png') }}" class="logo-img-left">
                        @endif
                    </td>
                    <td class="header-logo-right">
                        @if(file_exists(public_path('logo.png')))
                            <img src="{{ asset('logo.png') }}" class="logo-img-right">
                        @endif
                    </td>
                </tr>
            </table>

            <!-- 2. Category Title Banner (Category-wise Background Color) -->
            <div class="title-banner" style="background-color: {{ $bannerColor }} !important;">
                <h1>Statement of Marks</h1>
            </div>

            <!-- 3. Candidate Details & Photograph Layout (Exact Side-by-Side Design) -->
            <table class="candidate-table">
                <tr>
                    <!-- Left: Details Grid -->
                    <td class="details-col">
                        <table class="details-grid">
                            <tr>
                                <td class="label-cell">Category</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    {{ strtoupper($student->category->name ?? 'N/A') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">Centre of Examination</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    {{ strtoupper($student->centre->name ?? $student->school->name ?? 'N/A') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">Name of Candidate</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    {{ strtoupper($student->name) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">Parentage</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    {{ strtoupper($student->father_name ?? 'N/A') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">Class</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    {{ strtoupper($student->class->name ?? 'N/A') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">Date of Examination</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    30-08-2026
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">Examination Session</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    {{ strtoupper($student->examination->name ?? 'EXAMINATION') }}
                                    ({{ $student->examination->academic_year ?? '2026-2027' }})
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- Right: Reg No & Photo Box -->
                    <td class="photo-col">
                        <div class="reg-no-label">
                            Reg. No
                        </div>
                        <div class="reg-no-box">
                            {{ $student->registration_number }}
                        </div>
                        <div class="photo-box">
                            @if($photoSrc)
                                <img src="{{ $photoSrc }}" alt="Candidate Photo" class="photo-img">
                            @else
                                <div class="photo-placeholder">
                                    AFFIX<br>PHOTO
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <!-- 4. Result Scores Table (Matching Grid Design) -->
            <div class="scores-table-wrap">
                <table class="scores-grid">
                    <thead>
                        <tr style="background-color: #f8fafc; text-align: left;">
                            <th style="font-weight: bold; width: 45%;">Assessment Details</th>
                            <th style="font-weight: bold; text-align: center; width: 18%;">Marks Obtained</th>
                            <th style="font-weight: bold; text-align: center; width: 18%;">Maximum Marks</th>
                            <th style="font-weight: bold; text-align: center; width: 19%;">Grade / Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($result && $result->subject_marks && count($result->subject_marks) > 0)
                            @foreach($result->subject_marks as $subjectName => $data)
                                <tr>
                                    <td style="font-weight: bold;">{{ $subjectName }}</td>
                                    <td style="text-align: center; font-family: monospace; font-weight: bold;">
                                        {{ $data['marks'] }}</td>
                                    <td style="text-align: center; font-family: monospace;">{{ $data['max'] }}</td>
                                    <td style="text-align: center; font-weight: bold;">—</td>
                                </tr>
                            @endforeach
                        @endif

                        <tr style="background-color: #ffffff; font-weight: bold;">
                            <td>Total Aggregate Marks</td>
                            <td style="text-align: center; font-family: monospace; font-weight: bold; color: #000000;">
                                {{ $result ? $result->marks_obtained : '0' }}
                            </td>
                            <td style="text-align: center; font-family: monospace;">
                                {{ $result ? $result->max_marks : '500' }}
                            </td>
                            <td style="text-align: center;">
                                <span style="font-weight: bold; color: #1e3a8a;">
                                    {{ $result && $result->grade ? $result->grade : ($result && $result->percentage ? $result->percentage . '%' : '—') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 5. Overall Result Summary & Status (Exact Side-by-Side Design) -->
            <table class="summary-table">
                <tr>
                    <td class="summary-left">
                        @php
                            $remarksText = $result ? $result->remarks : null;
                            if (empty($remarksText) && $result) {
                                if ($result->status === 'Pass') {
                                    $remarksText = 'Qualified For Second Round Examination';
                                } elseif ($result->status === 'Fail') {
                                    $remarksText = 'Not Qualified For Second Round Examination';
                                }
                            }
                        @endphp
                        <div style="line-height: 1.5;">
                            <strong>Percentage:</strong> {{ $result ? $result->percentage : '0' }}%<br>
                            <strong>Final Grade:</strong> {{ $result ? $result->grade : 'N/A' }}<br>
                            @if($remarksText)
                                <strong>Remarks:</strong> {{ $remarksText }}
                            @endif
                        </div>
                    </td>
                    <td class="summary-right">
                        @php
                            $statusText = $result ? $result->status : 'Absent';
                            $statusBorder = '#10b981';
                            $statusBg = '#ecfdf5';
                            $statusColor = '#047857';
                            if ($statusText === 'Fail') {
                                $statusBorder = '#f43f5e';
                                $statusBg = '#fff1f2';
                                $statusColor = '#be123c';
                            } elseif ($statusText === 'Absent' || $statusText === 'Withheld') {
                                $statusBorder = '#f59e0b';
                                $statusBg = '#fef3c7';
                                $statusColor = '#b45309';
                            }
                        @endphp
                        <div class="status-badge-box"
                            style="display: inline-block; border: 2px solid {{ $statusBorder }}; background-color: {{ $statusBg }}; color: {{ $statusColor }}; padding: 8px 24px; border-radius: 8px; font-weight: 800; font-size: 16px; letter-spacing: 2px; text-transform: uppercase;">
                            {{ $statusText }}
                        </div>
                    </td>
                </tr>
            </table>

            <hr style="border: 0; border-top: 1px solid #000000; margin: 10px 0;">

            <!-- 6. Barcode & Controller Signature Row (Exact Side-by-Side Design) -->
            <table class="barcode-sig-table">
                <tr>
                    <td class="barcode-col">
                        <img src="{{ $barcodeSvg }}" class="barcode-svg-img">
                    </td>
                    <td class="signature-col">
                        <div style="display: inline-block; text-align: center;">
                            @if($signPath)
                                <img src="{{ $signPath }}" alt="Controller Signature" class="sign-img">
                            @else
                                <svg width="100" height="28" viewBox="0 0 100 28" xmlns="http://www.w3.org/2000/svg"
                                    style="display: block; margin: 0 auto 2px auto;">
                                    <path d="M5 18 C20 8, 35 22, 50 10 C60 5, 75 20, 90 12" fill="none" stroke="#1d4ed8"
                                        stroke-width="1.8" />
                                    <path d="M30 20 L75 6" fill="none" stroke="#1d4ed8" stroke-width="1.4" />
                                </svg>
                            @endif
                            <div class="controller-label">
                                Controller Of Examination
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- 7. Bottom Footnote -->
            <hr style="border: 0; border-top: 1px solid #cbd5e1; margin: 8px 0 5px;">
            <table class="footnote-table"
                style="width: 100%; border-collapse: collapse; font-size: 9px; color: #475569;">
                <tr>
                    <td style="width: 50%; text-align: left; vertical-align: top;">
                        Board of Examinations<br>
                        YES Academia
                    </td>
                    <td style="width: 50%; text-align: right; vertical-align: top;">
                        yesgeniusacademia@gmail.com
                    </td>
                </tr>
            </table>

        </div>

        <!-- Action Bar Under Marksheet (Hidden on print) -->
        <div class="action-bar w-full max-w-[780px] flex items-center justify-between gap-4 mt-4 sm:mt-6 no-print">
            <a href="{{ route('results.check-form') }}"
                class="text-xs font-semibold text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Check Another Result
            </a>

            <button type="button" id="print-marksheet-btn"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/10 flex items-center gap-2 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0a2.25 2.25 0 0 1-2.25 2.25H8.59a2.25 2.25 0 0 1-2.25-2.25M17.66 18c.067-.179.1-.368.1-.562V12h1.5A2.25 2.25 0 0 0 17 9.75H7A2.25 2.25 0 0 0 4.75 12v5.438c0 .194.033.383.1.562m12.8 0H4.85M16.5 9.75V4.875c0-.621-.504-1.125-1.125-1.125h-6.75a1.125 1.125 0 0 0-1.125 1.125V9.75M8.25 12.5h.008v.008H8.25v-.008Zm3.75 0h.008v.008H12v-.008Z" />
                </svg>
                Print Statement of Marks
            </button>
        </div>
    </div>

    <!-- Public Footer Component (Hidden on print) -->
    <div class="w-full no-print">
        <x-public-footer page="results" />
    </div>

    <script @nonce>
        document.addEventListener('DOMContentLoaded', function () {
            var printBtn = document.getElementById('print-marksheet-btn');
            if (printBtn) {
                printBtn.addEventListener('click', function () {
                    window.print();
                });
            }
        });
    </script>
</body>

</html>