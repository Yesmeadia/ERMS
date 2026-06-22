<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $examinationId;
    private $schoolId;

    public function __construct($examinationId, $schoolId)
    {
        $this->examinationId = $examinationId;
        $this->schoolId = $schoolId;
    }

    public function model(array $row)
    {
        // Find class
        $class = ClassMaster::where('code', trim($row['class_code']))->where('status', true)->first();
        // Find category
        $category = CategoryMaster::where('code', trim($row['category_code']))->where('status', true)->first();

        if (!$class || !$category) {
            return null; // Skip if master data is invalid/inactive
        }

        // Parse date of birth
        $dob = $row['date_of_birth_yyyy_mm_dd'];
        if (is_numeric($dob)) {
            // Excel serial date format
            $dobDate = Date::excelToDateTimeObject($dob);
        } else {
            try {
                $dobDate = Carbon::parse(trim($dob));
            } catch (\Exception $e) {
                $dobDate = Carbon::now();
            }
        }

        // Duplicate check 1: admission_number
        $duplicateAdmission = Student::where('school_id', $this->schoolId)
            ->where('examination_id', $this->examinationId)
            ->where('admission_number', trim($row['admission_number']))
            ->exists();
        if ($duplicateAdmission) {
            throw new \Exception("Student with admission number '" . trim($row['admission_number']) . "' is already registered in this school/exam session.");
        }

        // Duplicate check 2: name + dob + father_name
        $duplicatePerson = Student::where('school_id', $this->schoolId)
            ->where('examination_id', $this->examinationId)
            ->where('name', trim($row['student_name']))
            ->where('dob', $dobDate->format('Y-m-d'))
            ->where('father_name', trim($row['father_name']))
            ->exists();
        if ($duplicatePerson) {
            throw new \Exception("Student '" . trim($row['student_name']) . "' with the same Date of Birth and Father's Name is already registered in this school/exam session.");
        }

        return new Student([
            'school_id' => $this->schoolId,
            'examination_id' => $this->examinationId,
            'class_id' => $class->id,
            'category_id' => $category->id,
            'name' => trim($row['student_name']),
            'gender' => trim($row['gender']),
            'dob' => $dobDate->format('Y-m-d'),
            'father_name' => trim($row['father_name']),
            'mother_name' => trim($row['mother_name']),
            'mobile_number' => trim($row['mobile_number']),
            'admission_number' => trim($row['admission_number']),
            'status' => 'Draft',
        ]);
    }

    public function rules(): array
    {
        return [
            '*.student_name' => ['required', 'string', 'max:255'],
            '*.gender' => ['required', 'string', 'max:50'],
            '*.date_of_birth_yyyy_mm_dd' => ['required'],
            '*.father_name' => ['required', 'string', 'max:255'],
            '*.mother_name' => ['required', 'string', 'max:255'],
            '*.mobile_number' => ['required'],
            '*.admission_number' => ['required'],
            '*.class_code' => ['required', 'exists:classes,code'],
            '*.category_code' => ['required', 'exists:categories,code'],
        ];
    }
}
