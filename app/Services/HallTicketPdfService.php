<?php

namespace App\Services;

use App\Models\HallTicket;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DompdfWrapper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use RuntimeException;

class HallTicketPdfService
{
    /**
     * Prepare student model, QR payload, and verification URL.
     */
    public function prepareStudentData(Student $student): array
    {
        if ($student->status !== 'Hall Ticket Issued') {
            throw new RuntimeException("Student ID #{$student->id} ({$student->name}) does not have 'Hall Ticket Issued' status.");
        }

        if (empty($student->hall_ticket_number)) {
            throw new RuntimeException("Student ID #{$student->id} ({$student->name}) is missing an assigned hall ticket number.");
        }

        if (empty($student->centre_id)) {
            throw new RuntimeException("Student ID #{$student->id} ({$student->name}) is missing an assigned examination centre.");
        }

        $hallTicket = $student->hallTicket;
        if (!$hallTicket) {
            $hallTicket = HallTicket::create([
                'student_id' => $student->id,
                'hallticket_no' => $student->hall_ticket_number,
                'qr_token' => bin2hex(random_bytes(32)),
                'issue_date' => now(),
                'status' => 'Issued',
            ]);
        }

        $qrPayload = json_encode([
            'student_id' => $student->id,
            'hallticket_no' => $student->hall_ticket_number,
            'exam_id' => $student->examination_id,
            'token' => $hallTicket->qr_token,
        ]);

        $verifyUrl = route('verification.hall-ticket', $student->hall_ticket_number);
        $qrSvg = QrCode::size(220)->margin(2)->generate($qrPayload);
        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        return [
            'student' => $student,
            'qrDataUri' => $qrDataUri,
            'verifyUrl' => $verifyUrl,
        ];
    }

    /**
     * Render a single student Hall Ticket PDF.
     */
    public function generateSinglePdf(Student $student): DompdfWrapper
    {
        $student->loadMissing(['school', 'class', 'category', 'examination', 'hallTicket', 'centre']);
        $prepared = $this->prepareStudentData($student);

        $pdf = Pdf::loadView('pdf.hall-ticket', [
            'student' => $prepared['student'],
            'qrDataUri' => $prepared['qrDataUri'],
            'verifyUrl' => $prepared['verifyUrl'],
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        return $pdf;
    }

    /**
     * Render a bulk student Hall Ticket PDF part.
     */
    public function generateBulkPdf(iterable $students): DompdfWrapper
    {
        $studentsData = [];
        foreach ($students as $student) {
            $studentsData[] = $this->prepareStudentData($student);
        }

        $pdf = Pdf::loadView('pdf.hall-tickets-bulk', compact('studentsData'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        return $pdf;
    }
}
