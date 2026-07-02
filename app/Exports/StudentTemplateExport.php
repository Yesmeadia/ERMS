<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentTemplateExport implements FromArray, WithHeadings, WithStrictNullComparison, WithStyles
{
    public function headings(): array
    {
        // IMPORTANT: These headers are auto-converted to snake_case by WithHeadingRow.
        // "Student Name"              -> student_name
        // "Gender"                    -> gender
        // "Date Of Birth Yyyy Mm Dd"  -> date_of_birth_yyyy_mm_dd
        // "Father Name"               -> father_name
        // "Mother Name"               -> mother_name
        // "Mobile Number"             -> mobile_number
        // "Class Code"                -> class_code
        // "Category Code"             -> category_code
        return [
            'Student Name',
            'Gender',
            'Date Of Birth Yyyy Mm Dd',
            'Father Name',
            'Mother Name',
            'Mobile Number',
            'Class Code',
            'Category Code',
        ];
    }

    public function array(): array
    {
        // Sample row — date must be in YYYY-MM-DD format; Class Code and Category Code
        // must match an active record in the classes / categories tables.
        return [
            [
                'MOHD ANAS',
                'MALE',
                '2012-12-10',
                'MOHD IMRAN',
                'SULTANA',
                '9876543210',
                'C3',
                'RB3',
            ]
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Bold white text on indigo background for the header row
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'],
                ],
            ],
        ];
    }
}
