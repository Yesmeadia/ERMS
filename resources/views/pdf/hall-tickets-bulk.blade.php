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

            $text = strtoupper($text);
            $sanitized = '';
            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];
                if (isset($patterns[$char])) {
                    $sanitized .= $char;
                } else {
                    $sanitized .= ' ';
                }
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
                if (!isset($patterns[$char])) {
                    continue;
                }
                $pattern = $patterns[$char];

                for ($j = 0; $j < strlen($pattern); $j++) {
                    if ($pattern[$j] == '1') {
                        $svg .= '<rect x="' . ($x * $scale) . '" y="0" width="' . $scale . '" height="' . $barHeight . '" fill="#000000" />';
                    }
                    $x++;
                }
                if ($i < $len - 1) {
                    $x += $gapWidth;
                }
            }

            $svg .= '<text x="' . ($svgWidth / 2) . '" y="' . ($barHeight + 8) . '" font-family="Metropolis, sans-serif" font-size="7" fill="#000000" text-anchor="middle" letter-spacing="1.5">' . $text . '</text>';
            $svg .= '</svg>';

            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
    }

    $leftLogoPath = file_exists(public_path('logob.png')) ? public_path('logob.png') : (file_exists(public_path('logo.png')) ? public_path('logo.png') : null);
    $rightLogoPath = file_exists(public_path('logo.png')) ? public_path('logo.png') : null;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bulk Hall Tickets</title>
    <style>
        @font-face {
            font-family: 'Metropolis';
            font-weight: 400;
            src: url('https://cdn.jsdelivr.net/npm/@typehaus/metropolis@1.0.0/Metropolis-Regular.woff2') format('woff2'),
                url('https://cdn.jsdelivr.net/npm/@typehaus/metropolis@1.0.0/Metropolis-Regular.woff') format('woff');
        }

        @font-face {
            font-family: 'Metropolis';
            font-weight: 700;
            src: url('https://cdn.jsdelivr.net/npm/@typehaus/metropolis@1.0.0/Metropolis-Bold.woff2') format('woff2'),
                url('https://cdn.jsdelivr.net/npm/@typehaus/metropolis@1.0.0/Metropolis-Bold.woff') format('woff');
        }

        body {
            font-family: 'Metropolis', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #000000;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .page-break {
            page-break-before: always;
        }

        .container {
            padding: 20px 25px;
            position: relative;
            background-color: #ffffff;
            height: 980px;
            box-sizing: border-box;
        }

        /* Top Header logos */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .header-logo-left {
            width: 45%;
            text-align: left;
            vertical-align: middle;
        }

        .header-logo-right {
            width: 45%;
            text-align: right;
            vertical-align: middle;
        }

        .header-spacer {
            width: 10%;
        }

        /* Green Title Banner */
        .title-banner {
            background-color: #3e6b27;
            text-align: center;
            padding: 6px 0;
            margin-bottom: 25px;
            margin-left: -70px;
            margin-right: -70px;
        }

        .title-banner h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin: 0;
            text-transform: uppercase;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Content section: Details and Photo side by side */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
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

        /* Details Grid Table */
        .details-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .details-grid td {
            border: 1px solid #000000;
            padding: 7px 10px;
            vertical-align: middle;
            font-size: 12px;
            color: #000000;
        }

        .label-cell {
            font-weight: bold;
            width: 32%;
            background-color: #ffffff;
        }

        .value-cell {
            width: 68%;
        }

        /* Right column Reg No & Photo */
        .reg-no-label {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 1px;
            margin-top: -2px;
            text-align: left;
            color: #000000;
        }

        .reg-no-box {
            border: 1px solid #000000;
            padding: 0 1px;
            text-align: center;
            font-family: 'Metropolis', monospace;
            font-size: 22px;
            font-weight: bold;
            color: #d32f2f;
            background-color: #ffffff;
            margin-bottom: 4px;
            margin-top: -1px;
            height: 30px;
            line-height: 30px;
            width: 100%;
            display: block;
            box-sizing: border-box;
        }

        .photo-box {
            border: 1px solid #000000;
            width: 100%;
            height: 170px;
            /* passport size: 35mm x 45mm @ 96dpi */
            background-color: #ffffff;
            position: relative;
            text-align: center;
            box-sizing: border-box;
            display: block;
            overflow: hidden;
        }

        .photo-img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            object-position: top center;
            display: block;
        }

        .photo-placeholder {
            padding-top: 65px;
            font-size: 9px;
            color: #94a3b8;
            font-weight: bold;
            border: 1px dashed #cbd5e1;
            height: 100%;
            box-sizing: border-box;
            background-color: #f8fafc;
        }

        /* Signatures and QR Code Row */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .sig-text-cell {
            vertical-align: bottom;
            height: 80px;
            padding-bottom: 4px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 11px;
            color: #000000;
        }

        .qr-cell {
            vertical-align: top;
            text-align: right;
        }

        .separator-line {
            border: 0;
            border-top: 1px solid #000000;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        /* Instructions Section */
        .instructions-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .instructions-title {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            color: #000000;
            letter-spacing: 0.5px;
        }

        .instructions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .bullet-cell {
            width: 3%;
            vertical-align: top;
            padding-bottom: 6px;
            font-weight: bold;
            font-size: 18px;
            color: #000000;
            line-height: 1.2;
        }

        .text-cell {
            width: 97%;
            vertical-align: top;
            padding-bottom: 6px;
            font-size: 12px;
            line-height: 1.4;
            color: #000000;
        }

        /* Barcode & Controller Signature Row */
        .barcode-sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .barcode-cell {
            width: 50%;
            vertical-align: middle;
            text-align: left;
        }

        .controller-sig-cell {
            width: 50%;
            vertical-align: middle;
            text-align: right;
        }

        .controller-sig-container {
            display: inline-block;
            text-align: center;
        }

        .controller-label {
            font-weight: bold;
            font-size: 13.5px;
            color: #000000;
            margin-top: 2px;
        }

        /* Footer */
        .footer-separator {
            border: 0;
            border-top: 1px solid #cbd5e1;
            margin-top: 10px;
            margin-bottom: 8px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-left {
            width: 50%;
            vertical-align: top;
            font-size: 9px;
            line-height: 1.3;
            color: #475569;
            text-align: left;
        }

        .footer-right {
            width: 50%;
            vertical-align: top;
            text-align: right;
            font-size: 9px;
            color: #475569;
        }
    </style>
</head>

<body>

    @foreach($studentsData as $data)
        @php
            $student = $data['student'];
            $qrDataUri = $data['qrDataUri'];
            $verifyUrl = $data['verifyUrl'];

            $photoPath = null;
            if ($student->photograph) {
                if (file_exists(public_path('storage/' . $student->photograph))) {
                    $photoPath = public_path('storage/' . $student->photograph);
                } elseif (file_exists(storage_path('app/public/' . $student->photograph))) {
                    $photoPath = storage_path('app/public/' . $student->photograph);
                }
            }

            // Determine banner color based on category name
            $categoryName = strtoupper($student->category->name ?? '');
            if (str_starts_with($categoryName, 'RAINBOW')) {
                $bannerColor = '#2e7d32'; // RAINBOW 3/4/5 → green
            } elseif (str_starts_with($categoryName, 'PLANET')) {
                $bannerColor = '#c62828'; // PLANET → red
            } elseif (str_starts_with($categoryName, 'GALAXY')) {
                $bannerColor = '#1565c0'; // GALAXY HS/HSS → blue
            } else {
                $bannerColor = '#3e6b27'; // default green
            }
        @endphp

        <div class="container">

            {{-- Header Section --}}
            <table style="margin: 0 auto; border-collapse: collapse; margin-bottom: 12px;">
                <tr>
                    <td style="padding-right: 40px; vertical-align: middle; text-align: right;">
                        @if($leftLogoPath)
                            <img src="{{ $leftLogoPath }}" style="height: 52px; max-width: 220px;">
                        @endif
                    </td>
                    <td style="padding-left: 40px; vertical-align: middle; text-align: left;">
                        @if($rightLogoPath)
                            <img src="{{ $rightLogoPath }}" style="height: 98px; max-width: 280px;">
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Banner (color based on category) --}}
            <div class="title-banner" style="background-color: {{ $bannerColor }};">
                <h1>Hall Ticket</h1>
            </div>

            {{-- Details & Photograph Side-by-Side --}}
            <table class="content-table">
                <tr>
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
                                <td class="value-cell" style="font-weight: bold;">{{ strtoupper($student->centre->name ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="label-cell">Name of Candidate</td>
                                <td class="value-cell" style="font-weight: bold;">{{ strtoupper($student->name) }}</td>
                            </tr>
                            <tr>
                                <td class="label-cell">Parentage</td>
                                <td class="value-cell" style="font-weight: bold;">
                                    {{ strtoupper($student->father_name ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="label-cell">Class</td>
                                <td class="value-cell" style="font-weight: bold;">{{ strtoupper($student->class->name ?? 'N/A') }}</td>
                            </tr>
                            @php
                                $examDate = \Carbon\Carbon::parse('2026-07-30');
                                $examTime = '10:00 AM to 12:30 PM';
                            @endphp
                            <tr>
                                <td class="label-cell">Date of Examination</td>
                                <td class="value-cell" style="font-weight: bold;">{{ $examDate->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <td class="label-cell">Time of Examination</td>
                                <td class="value-cell" style="font-weight: bold;">{{ $examTime }}</td>
                            </tr>
                        </table>
                    </td>

                    <td class="photo-col">
                        <div class="reg-no-label">Reg. No</div>
                        <div class="reg-no-box">
                            {{ $student->registration_number }}
                        </div>
                        <div class="photo-box">
                            @if($photoPath)
                                <img src="{{ $photoPath }}" class="photo-img">
                            @else
                                <div class="photo-placeholder">
                                    AFFIX<br>PHOTO
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Signatures & QR Code Row --}}
            <table class="signatures-table">
                <tr>
                    <td class="sig-text-cell" style="width: 38%; text-align: left;">
                        <span class="sig-label">Signature Of Invigilator</span>
                    </td>
                    <td class="sig-text-cell" style="width: 38%; text-align: center;">
                        <span class="sig-label">Signature Of Candidate</span>
                    </td>
                    <td class="qr-cell" style="width: 24%; vertical-align: middle; text-align: right;">
                        @if(!empty($qrDataUri))
                            <img src="{{ $qrDataUri }}" style="width: 110px; height: 110px; display: inline-block;">
                        @endif
                    </td>
                </tr>
            </table>

            <hr class="separator-line">

            {{-- Instructions Section --}}
            <div class="instructions-header">
                <span class="instructions-title">Instructions For Candidates</span>
            </div>
            <table class="instructions-table">
                <tr>
                    <td class="bullet-cell">&bull;</td>
                    <td class="text-cell">Candidates must ensure they have their hall tickets with them as it is mandatory
                        for entry into the examination hall.</td>
                </tr>
                <tr>
                    <td class="bullet-cell">&bull;</td>
                    <td class="text-cell">Be on time: Ensure you are present at the exam center at least 15-30 minutes
                        before the scheduled time. This will give you enough time to settle down, read instructions, and be
                        prepared for the test.</td>
                </tr>
                <tr>
                    <td class="bullet-cell">&bull;</td>
                    <td class="text-cell">Listen to instructions: Pay careful attention to the instructions given by the
                        invigilator before starting the test. Understand the rules and regulations of the test and follow
                        them.</td>
                </tr>
                <tr>
                    <td class="bullet-cell">&bull;</td>
                    <td class="text-cell">Cool of time is 15 minutes: utilize this time to read the questions.</td>
                </tr>
                <tr>
                    <td class="bullet-cell">&bull;</td>
                    <td class="text-cell">Read questions carefully: Read each question carefully and understand what it is
                        asking for. Don't rush through the questions; take enough time to answer them correctly.</td>
                </tr>
                <tr>
                    <td class="bullet-cell">&bull;</td>
                    <td class="text-cell">Candidates will not be allowed to leave the examination center before the
                        examination is over.</td>
                </tr>
                <tr>
                    <td class="bullet-cell">&bull;</td>
                    <td class="text-cell">Submit the test: Complete your answer sheet on time, wait for the invigilator to
                        collect your answer sheet.</td>
                </tr>
            </table>

            {{-- Barcode & Controller Signature Row --}}
            <table class="barcode-sig-table" style="margin-top: 25px;">
                <tr>
                    <td class="barcode-cell">
                        <div style="display: inline-block;">
                            <img src="{{ generateBarcodeSVG($student->hall_ticket_number) }}"
                                style="height: 38px; width: auto; display: block;">
                        </div>
                    </td>
                    <td class="controller-sig-cell">
                        <div class="controller-sig-container">
                            @php $signPath = file_exists(public_path('sign.png')) ? public_path('sign.png') : null; @endphp
                            @if($signPath)
                                <img src="{{ $signPath }}"
                                    style="height: 60px; width: auto; display: block; margin: 0 auto 2px auto;">
                            @else
                                <svg width="100" height="28" viewBox="0 0 100 28" xmlns="http://www.w3.org/2000/svg"
                                    style="display: block; margin: 0 auto 2px auto;">
                                    <path d="M5 18 C20 8, 35 22, 50 10 C60 5, 75 20, 90 12" fill="none" stroke="#1d4ed8"
                                        stroke-width="1.8" />
                                    <path d="M30 20 L75 6" fill="none" stroke="#1d4ed8" stroke-width="1.4" />
                                </svg>
                            @endif
                            <div class="controller-label">Controller Of Examination</div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Footer --}}
            <hr class="footer-separator">
            <table class="footer-table">
                <tr>
                    <td class="footer-left">
                        Board of Examinations<br>
                        YES Academia
                    </td>
                    <td class="footer-right">
                        yesgeniusacademia@gmail.com
                    </td>
                </tr>
            </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>

</html>