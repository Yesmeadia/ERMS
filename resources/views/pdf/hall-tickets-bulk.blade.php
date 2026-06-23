<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Hall Tickets</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .page-break {
            page-break-after: always;
        }
        .container {
            border: 2px solid #334155;
            padding: 25px;
            border-radius: 12px;
            position: relative;
            background-color: #ffffff;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            background-color: #f1f5f9;
            padding: 6px 10px;
            margin-bottom: 12px;
            border-left: 3px solid #3b82f6;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .label {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
        }
        .value {
            color: #0f172a;
            font-size: 13px;
            font-weight: 600;
        }
        .photo-box {
            width: 120px;
            height: 140px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
            background-color: #f8fafc;
        }
        .photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            font-size: 10px;
            color: #94a3b8;
            padding: 20px 5px;
        }
        .qr-box {
            text-align: right;
            vertical-align: bottom;
            width: 140px;
        }
        .qr-img {
            width: 130px;
            height: 130px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background-color: #ffffff;
        }
        .footer-table {
            width: 100%;
            margin-top: 50px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 20px;
        }
        .signature-area {
            text-align: center;
            width: 33%;
        }
        .signature-line {
            width: 80%;
            border-bottom: 1px solid #94a3b8;
            margin: 0 auto 8px auto;
            height: 40px;
        }
        .signature-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .instructions-list {
            margin: 0;
            padding-left: 20px;
            font-size: 11px;
            color: #475569;
        }
        .instructions-list li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

@foreach($studentsData as $data)
    @php
        $student = $data['student'];
        $qrDataUri = $data['qrDataUri'];
        $verifyUrl = $data['verifyUrl'];
    @endphp

    <div class="container">
        {{-- Header --}}
        <table class="header-table">
            <tr>
                <td style="width: 75%;">
                    <div class="header-title">Board of Examinations</div>
                    <div class="header-subtitle">Examination Registration Management System (ERMS)</div>
                    <div style="font-size: 14px; font-weight: bold; color: #1e293b; margin-top: 8px;">
                        {{ $student->examination->name }} - HALL TICKET
                    </div>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: top;">
                    <div style="font-size: 11px; color: #64748b; font-weight: bold;">ACADEMIC YEAR</div>
                    <div style="font-size: 14px; font-weight: bold; color: #0f172a;">{{ $student->examination->academic_year }}</div>
                </td>
            </tr>
        </table>

        {{-- Details Area --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <tr>
                {{-- Info Columns (Left) --}}
                <td style="width: 78%; vertical-align: top; padding-right: 15px;">
                    <div class="section-title">Candidate Details</div>
                    
                    <table class="info-table">
                        <tr>
                            <td style="width: 50%;">
                                <span class="label">Candidate Name</span>
                                <span class="value">{{ $student->name }}</span>
                            </td>
                            <td style="width: 50%;">
                                <span class="label">Mobile Number</span>
                                <span class="value">{{ $student->mobile_number }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="label">Date of Birth</span>
                                <span class="value">{{ $student->dob->format('d M Y') }}</span>
                            </td>
                            <td>
                                <span class="label">Gender</span>
                                <span class="value">{{ $student->gender }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="label">Father Name</span>
                                <span class="value">{{ $student->father_name }}</span>
                            </td>
                            <td>
                                <span class="label">Mother Name</span>
                                <span class="value">{{ $student->mother_name }}</span>
                            </td>
                        </tr>
                    </table>

                    <div class="section-title">Academic & Exam Details</div>
                    
                    <table class="info-table">
                        <tr>
                            <td style="width: 50%;">
                                <span class="label">School Code & Name</span>
                                <span class="value">[{{ $student->school->code }}] {{ $student->school->name }}</span>
                            </td>
                            <td style="width: 50%;">
                                <span class="label">Class</span>
                                <span class="value">{{ $student->class->name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="label">Registration Number</span>
                                <span class="value" style="font-family: monospace; font-size: 14px;">{{ $student->registration_number }}</span>
                            </td>
                            <td>
                                <span class="label">Category</span>
                                <span class="value">{{ $student->category->name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="label">Hall Ticket Number</span>
                                <span class="value" style="font-family: monospace; font-size: 15px; color: #1e3a8a; font-weight: bold;">
                                    {{ $student->hall_ticket_number }}
                                </span>
                            </td>
                            <td>
                                <span class="label">Examination Centre</span>
                                <span class="value">Assigned School Centre</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Photograph (Right) --}}
                <td style="width: 22%; vertical-align: top; text-align: center; padding-left: 10px;">
                    <div style="width: 120px; height: 150px; border: 2px solid #cbd5e1; margin: 0 auto; overflow: hidden; background-color: #f8fafc;">
                        @php
                            $photoPath = null;
                            if ($student->photograph) {
                                if (file_exists(public_path('storage/' . $student->photograph))) {
                                    $photoPath = public_path('storage/' . $student->photograph);
                                } elseif (file_exists(storage_path('app/public/' . $student->photograph))) {
                                    $photoPath = storage_path('app/public/' . $student->photograph);
                                }
                            }
                        @endphp
                        @if($photoPath)
                            <img src="{{ $photoPath }}" style="width: 120px; height: 150px;">
                        @else
                            <div style="padding: 30px 5px; text-align: center; font-size: 10px; color: #94a3b8;">
                                <strong>AFFIX<br>CANDIDATE<br>PHOTO</strong><br><br>
                                <span style="font-size: 8px; color: #cbd5e1;">120 x 150px</span>
                            </div>
                        @endif
                    </div>
                    <div style="margin-top: 15px; text-align: center;">
                        @if(!empty($qrDataUri))
                            <img src="{{ $qrDataUri }}" class="qr-img">
                            <div style="font-size: 8px; color: #94a3b8; margin-top: 4px; text-transform: uppercase; font-weight: bold;">Scan to Verify</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Instructions Section --}}
        <div class="section-title" style="margin-top: 5px;">Instructions to Candidates</div>
        <ul class="instructions-list">
            <li>Candidates must bring this Hall Ticket along with school identification card to the examination hall.</li>
            <li>Report to the examination center at least 30 minutes before the scheduled exam start time.</li>
            <li>No electronic gadgets, cellular phones, smartwatches, or calculators are permitted inside the examination room.</li>
            <li>Write your Registration Number and Hall Ticket Number clearly on all answer sheets.</li>
            <li>Tampering with any information on the Hall Ticket or QR code will lead to immediate disqualification.</li>
        </ul>

        {{-- Signatures --}}
        <table class="footer-table">
            <tr>
                <td class="signature-area">
                    <div class="signature-line"></div>
                    <div class="signature-label">Candidate Signature</div>
                </td>
                <td class="signature-area">
                    <div class="signature-line"></div>
                    <div class="signature-label">School Headmaster</div>
                </td>
                <td class="signature-area">
                    <div class="signature-line">
                        <div style="font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 13px; color: #1e3a8a; padding-top: 15px; text-align: center;">BOARD SEC.</div>
                    </div>
                    <div class="signature-label">Controller of Examinations</div>
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
