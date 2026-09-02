<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ResultTemplateExport implements FromArray, WithHeadings, WithStrictNullComparison, WithStyles, ShouldAutoSize
{
    protected $examinationId;

    public function __construct($examinationId = null)
    {
        $this->examinationId = $examinationId;
    }

    public function headings(): array
    {
        return [
            'Registration Number',
            'Marks Obtained',
            'Max Marks',
            'Status',
        ];
    }

    public function array(): array
    {
        // Fetch up to 3 real candidates from the selected exam to make the template ready-to-use
        $sampleStudents = null;
        if ($this->examinationId) {
            $sampleStudents = Student::where('examination_id', $this->examinationId)
                ->whereIn('status', ['Hall Ticket Issued', 'Approved'])
                ->take(3)
                ->get();
        }

        if (!$sampleStudents || $sampleStudents->isEmpty()) {
            $sampleStudents = Student::whereIn('status', ['Hall Ticket Issued', 'Approved'])
                ->take(3)
                ->get();
        }

        if ($sampleStudents && $sampleStudents->count() >= 2) {
            $sampleRows = [];
            $demoData = [
                ['marks' => '430', 'max' => '500', 'status' => 'Pass'],
                ['marks' => '385', 'max' => '500', 'status' => 'Pass'],
                ['marks' => '145', 'max' => '500', 'status' => 'Fail'],
            ];

            foreach ($sampleStudents as $idx => $student) {
                $demo = $demoData[$idx % count($demoData)];
                $sampleRows[] = [
                    $student->registration_number ?? 'REG' . (10001 + $idx),
                    $demo['marks'],
                    $demo['max'],
                    $demo['status'],
                ];
            }
            return $sampleRows;
        }

        // Fallback default demo rows
        return [
            [
                'REG20260001',
                '425',
                '500',
                'Pass',
            ],
            [
                'REG20260002',
                '380',
                '500',
                'Pass',
            ],
            [
                'REG20260003',
                '140',
                '500',
                'Fail',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Style the header row with Indigo background, white text, bold font, and centered alignment
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11,
                    'name' => 'Calibri',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'], // Indigo-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
