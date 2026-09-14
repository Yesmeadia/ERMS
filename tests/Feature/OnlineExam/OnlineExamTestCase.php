<?php

namespace Tests\Feature\OnlineExam;

use App\Enums\ExamStatus;
use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\OnlineExam;
use App\Models\OnlineExamStudent;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class OnlineExamTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected School $testSchool;

    protected ClassMaster $testClass;

    protected CategoryMaster $testCategory;

    protected Examination $testExamination;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'exam-admin', 'guard_name' => 'web']);

        $this->testSchool = School::create([
            'name' => 'Model High School',
            'code' => 'MHS'.rand(100, 999),
            'address' => '123 Education Lane',
            'zone' => 'North Zone',
            'state' => 'State',
            'contact_person' => 'Headmaster',
            'mobile_number' => '9876543210',
            'email' => 'mhs'.rand(100, 999).'@test.com',
            'status' => true,
        ]);

        $this->testClass = ClassMaster::create([
            'name' => 'Standard 10',
            'status' => true,
        ]);

        $this->testCategory = CategoryMaster::create([
            'name' => 'Senior Secondary',
            'code' => 'SS'.rand(10, 99),
            'description' => 'Secondary students',
            'status' => true,
        ]);

        $this->testExamination = Examination::create([
            'name' => 'State Scholarship Exam',
            'academic_year' => '2026-2027',
            'registration_start_date' => '2026-01-01',
            'registration_end_date' => '2026-12-31',
            'hall_ticket_release_date' => '2026-12-31',
            'status' => 'Registration Started',
        ]);

        $this->adminUser = User::create([
            'name' => 'Exam Controller',
            'email' => 'admin_'.rand(1000, 9999).'@erms.test',
            'password' => bcrypt('secret123'),
        ]);
        $this->adminUser->assignRole('super-admin');
    }

    /**
     * Helper to create a valid ERMS student.
     */
    protected function createStudent(array $attributes = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $this->testSchool->id,
            'class_id' => $this->testClass->id,
            'category_id' => $this->testCategory->id,
            'examination_id' => $this->testExamination->id,
            'name' => 'Student '.rand(100, 999),
            'gender' => 'Female',
            'dob' => '2008-05-15',
            'father_name' => 'Father Name',
            'mother_name' => 'Mother Name',
            'mobile_number' => '9876543210',
            'registration_number' => 'REG'.rand(10000, 99999),
            'status' => 'Approved',
        ], $attributes));
    }

    /**
     * Helper to create an Online Exam.
     */
    protected function createOnlineExam(array $attributes = []): OnlineExam
    {
        return OnlineExam::create(array_merge([
            'name' => 'Online Scholarship Examination',
            'code' => 'EXAM-'.rand(1000, 9999),
            'category_id' => $this->testCategory->id,
            'exam_date' => now()->toDateString(),
            'start_time' => '00:00',
            'end_time' => '23:59',
            'duration_minutes' => 60,
            'default_question_time_limit' => 60,
            'total_marks' => 100,
            'pass_marks' => 40,
            'enable_camera' => true,
            'enable_fullscreen' => true,
            'max_fullscreen_violations' => 3,
            'max_eligible_students' => 199,
            'status' => ExamStatus::PUBLISHED,
            'created_by' => $this->adminUser->id,
        ], $attributes));
    }

    /**
     * Helper to enroll a student into an online exam.
     */
    protected function enrollStudent(OnlineExam $exam, Student $student, array $attributes = []): OnlineExamStudent
    {
        return OnlineExamStudent::create(array_merge([
            'online_exam_id' => $exam->id,
            'student_id' => $student->id,
            'registration_number' => $student->registration_number,
            'is_eligible' => true,
            'enrolled_at' => now(),
        ], $attributes));
    }
}
