<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    use RemembersRowNumber;

    private $examinationId;
    private $schoolId;
    private array $seenInImport = [];

    public function __construct($examinationId, $schoolId)
    {
        $this->examinationId = $examinationId;
        $this->schoolId      = $schoolId;
    }

    public function model(array $row)
    {
        $rowNum = $this->getRowNumber();
        $rowPrefix = $rowNum ? "Row {$rowNum}: " : "";

        // Find class by name (case-insensitive via trim)
        $class = ClassMaster::where('name', trim($row['class_name']))->where('status', true)->first();

        // Find category by code
        $category = CategoryMaster::where('code', trim($row['category_code']))->where('status', true)->first();

        if (!$class || !$category) {
            // Return null to skip; invalid/inactive codes are already caught by validation rules below
            return null;
        }

        // --- Parse date of birth ---
        $dob = $row['date_of_birth_yyyy_mm_dd'];
        if (is_numeric($dob)) {
            // Excel serial date
            $dobDate = Date::excelToDateTimeObject($dob);
            $dobDate = Carbon::instance($dobDate);
        } else {
            try {
                // Supports YYYY-MM-DD, DD-MM-YYYY, DD/MM/YYYY, etc.
                $dobDate = Carbon::parse(str_replace('/', '-', trim($dob)));
            } catch (\Exception $e) {
                $dobDate = Carbon::now();
            }
        }

        $studentName  = trim($row['student_name']);
        $fatherName   = trim($row['father_name']);
        $formattedDob = $dobDate->format('Y-m-d');

        $uniqueKey = strtolower($studentName . '|' . $formattedDob . '|' . $fatherName);

        // Check 1: Duplicate entry within the SAME uploaded Excel file
        if (isset($this->seenInImport[$uniqueKey])) {
            $prevRow = $this->seenInImport[$uniqueKey];
            throw new \Exception("{$rowPrefix}Duplicate student '{$studentName}' (Father: '{$fatherName}', DOB: {$formattedDob}) found in uploaded Excel file (same record as Row {$prevRow}).");
        }
        $this->seenInImport[$uniqueKey] = $rowNum ?? 'Unknown';

        // Check 2: Duplicate check in DB (same student name + dob + father name in same school/exam)
        $duplicatePerson = Student::where('school_id', $this->schoolId)
            ->where('examination_id', $this->examinationId)
            ->where('name', $studentName)
            ->where('dob', $formattedDob)
            ->where('father_name', $fatherName)
            ->exists();

        if ($duplicatePerson) {
            throw new \Exception("{$rowPrefix}Student '{$studentName}' (Father: '{$fatherName}', DOB: {$formattedDob}) is already registered in this school/exam session in the system.");
        }

        return new Student([
            'school_id'      => $this->schoolId,
            'examination_id' => $this->examinationId,
            'class_id'       => $class->id,
            'category_id'    => $category->id,
            'name'           => $studentName,
            'gender'         => strtoupper(trim($row['gender'])),
            'dob'            => $formattedDob,
            'father_name'    => $fatherName,
            'mother_name'    => trim($row['mother_name']),
            'mobile_number'  => trim($row['mobile_number']),
            'status'         => 'Draft',
        ]);
    }

    /**
     * Validation rules applied per row.
     * Keys use the snake_case form that WithHeadingRow generates from the header row.
     */
    public function rules(): array
    {
        return [
            '*.student_name'             => ['required', 'string', 'max:255'],
            '*.gender'                   => ['required', 'string', 'max:50'],
            '*.date_of_birth_yyyy_mm_dd' => ['required'],
            '*.father_name'              => ['required', 'string', 'max:255'],
            '*.mother_name'              => ['required', 'string', 'max:255'],
            '*.mobile_number'            => ['required'],
            '*.class_name'               => ['required', 'exists:classes,name'],
            '*.category_code'            => ['required', 'exists:categories,code'],
        ];
    }

    /**
     * Human-readable custom error messages for validation failures.
     */
    public function customValidationMessages(): array
    {
        return [
            '*.student_name.required'             => 'Student Name is required.',
            '*.gender.required'                   => 'Gender is required.',
            '*.date_of_birth_yyyy_mm_dd.required' => 'Date of Birth (YYYY-MM-DD) is required.',
            '*.father_name.required'              => 'Father Name is required.',
            '*.mother_name.required'              => 'Mother Name is required.',
            '*.mobile_number.required'            => 'Mobile Number is required.',
            '*.class_name.required'               => 'Class Name is required.',
            '*.class_name.exists'                 => 'Class Name does not match any active class. Please check the name.',
            '*.category_code.required'            => 'Category Code is required.',
            '*.category_code.exists'              => 'Category Code does not match any active category. Please check the code.',
        ];
    }
}
