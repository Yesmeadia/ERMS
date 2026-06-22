<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class StudentTemplateExport implements FromArray, WithHeadings, WithStrictNullComparison
{
    public function headings(): array
    {
        return [
            'Student Name',
            'Gender',
            'Date of Birth DD-MM-YYYY',
            'Father Name',
            'Mother Name',
            'Mobile Number',
            'Class',
            'Category'
        ];
    }

    public function array(): array
    {
        // Provide a sample row
        return [
            [
                'MOHD ANAS',
                'MALE',
                '10-12-2012',
                'MOHD IMRAN',
                'SULTANA',
                '9876543210',
                '3RD',
                'RAINBOW 3'
            ]
        ];
    }
}
