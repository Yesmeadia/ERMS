<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\StudentResult;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultTemplateExport implements WithMultipleSheets
{
    protected $examinationId;

    public function __construct($examinationId = null)
    {
        $this->examinationId = $examinationId;
    }

    public function sheets(): array
    {
        return [
            new ResultMarksDataSheet($this->examinationId),
            new ResultGradingRulesSheet(),
        ];
    }
}

class ResultMarksDataSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStrictNullComparison, WithStyles, WithTitle
{
    protected $examinationId;

    public function __construct($examinationId = null)
    {
        $this->examinationId = $examinationId;
    }

    public function title(): string
    {
        return 'Marks Entry Template';
    }

    public function headings(): array
    {
        return [
            'Registration Number',
            'Candidate Name',
            'Category',
            'Marks Obtained',
            'Max Marks',
            'Remarks',
        ];
    }

    public function array(): array
    {
        $query = Student::with(['category', 'class']);
        if ($this->examinationId) {
            $query->where('examination_id', $this->examinationId);
        }
        $query->whereIn('status', ['Hall Ticket Issued', 'Approved']);

        $sampleStudents = $query->take(15)->get();

        if ($sampleStudents && $sampleStudents->count() >= 2) {
            $sampleRows = [];
            foreach ($sampleStudents as $idx => $student) {
                $catName = $student->category ? $student->category->name : 'General';
                $defaultMax = StudentResult::getDefaultMaxMarks($catName);

                // Sample marks demonstrating A+ or A
                $demoMarks = (int) round($defaultMax * ($idx % 2 === 0 ? 0.90 : 0.82));
                $sampleRows[] = [
                    $student->registration_number ?? 'REG' . (10001 + $idx),
                    $student->name ?? 'Candidate ' . ($idx + 1),
                    $catName,
                    $demoMarks,
                    $defaultMax,
                    $idx % 2 === 0 ? 'Excellent performance' : 'Good effort',
                ];
            }

            return $sampleRows;
        }

        // Fallback default demo rows showing Rainbow (40), Planets (50), Galaxy (60)
        return [
            [
                'REG-RBW-3001',
                'Sample Candidate (Rainbow 3)',
                'RAINBOW 3',
                36,
                40,
                'Excellent performance (A+)',
            ],
            [
                'REG-RBW-4002',
                'Sample Candidate (Rainbow 4)',
                'RAINBOW 4',
                32,
                40,
                'Good effort (A)',
            ],
            [
                'REG-PLN-5001',
                'Sample Candidate (Planet)',
                'PLANET',
                45,
                50,
                'Outstanding score (A+)',
            ],
            [
                'REG-PLN-5002',
                'Sample Candidate (Planet)',
                'PLANET',
                38,
                50,
                'Good performance (B+)',
            ],
            [
                'REG-GLX-9001',
                'Sample Candidate (Galaxy HS)',
                'GALAXY HS',
                52,
                60,
                'Top grade (A+)',
            ],
            [
                'REG-GLX-9002',
                'Sample Candidate (Galaxy HSS)',
                'GALAXY HSS (ARTS)',
                43,
                60,
                'Very good performance (A)',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
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

class ResultGradingRulesSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStrictNullComparison, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Category & Grading Rules';
    }

    public function headings(): array
    {
        return [
            'Category Group',
            'Categories Included',
            'Max Marks',
            'Grade A+ (Threshold)',
            'Grade A (Threshold)',
            'Grade B+ (Threshold)',
            'Grade B (Threshold)',
            'Below B (No Grade / Fail)',
        ];
    }

    public function array(): array
    {
        return [
            [
                'RAINBOW',
                'RAINBOW 3, RAINBOW 4, RAINBOW 5',
                40,
                '90% and Above (>= 36 marks)',
                '80% and Above (>= 32 marks)',
                '70% and Above (>= 28 marks)',
                '60% and Above (>= 24 marks)',
                'Below 60% (< 24 marks)',
            ],
            [
                'PLANETS',
                'PLANET',
                50,
                '90% and Above (>= 45 marks)',
                '80% and Above (>= 40 marks)',
                '70% and Above (>= 35 marks)',
                '60% and Above (>= 30 marks)',
                'Below 60% (< 30 marks)',
            ],
            [
                'GALAXY',
                'GALAXY HS, GALAXY HSS (ARTS), GALAXY HSS (SCIENCE)',
                60,
                '85% and Above (>= 51 marks)',
                '70% and Above (>= 42 marks)',
                '55% and Above (>= 33 marks)',
                '40% and Above (>= 24 marks)',
                'Below 40% (< 24 marks)',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
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
                    'startColor' => ['argb' => 'FF059669'], // Emerald-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
