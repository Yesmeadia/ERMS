<?php

namespace Tests\Feature;

use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResultManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected $school;

    protected $class;

    protected $catRainbow;

    protected $catGalaxy;

    protected $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $this->superAdmin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_test@erms.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->superAdmin->assignRole($role);

        $this->school = School::create([
            'name' => 'Test School',
            'code' => 'SCH999',
            'email' => 'school_test@erms.com',
            'address' => '123 Test Street',
            'city' => 'City',
            'state' => 'State',
            'zone' => 'Zone 1',
            'contact_person' => 'Principal',
            'mobile_number' => '9999999999',
            'status' => true,
        ]);

        $this->class = ClassMaster::create(['name' => 'Class 3', 'status' => true]);
        $this->catRainbow = CategoryMaster::create(['name' => 'RAINBOW 3', 'code' => 'RB3', 'status' => true]);
        $this->catGalaxy = CategoryMaster::create(['name' => 'GALAXY HS', 'code' => 'GLX_HS', 'status' => true]);

        $this->exam = Examination::create([
            'name' => 'Annual Exam 2027',
            'academic_year' => '2026-2027',
            'registration_start_date' => '2026-09-01',
            'registration_end_date' => '2026-11-30',
            'hall_ticket_release_date' => '2027-02-15',
            'status' => 'Ongoing',
        ]);
    }

    /**
     * Test storing result saves manual remarks and does not generate auto-remarks.
     */
    public function test_admin_can_store_result_with_manual_remarks(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->catRainbow->id,
            'examination_id' => $this->exam->id,
            'name' => 'Rainbow Candidate',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '9999999999',
            'status' => 'Hall Ticket Issued',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.results.store'), [
                'student_id' => $student->id,
                'marks_obtained' => 36, // 36/40 = 90% -> should be A+ for Rainbow
                'max_marks' => 40,
                'remarks' => 'Custom teacher remark here',
            ]);

        $response->assertRedirect(route('admin.results.index'));
        $response->assertSessionHas('success');

        $result = StudentResult::where('student_id', $student->id)->first();
        $this->assertNotNull($result);
        $this->assertEquals(36, $result->marks_obtained);
        $this->assertEquals(40, $result->max_marks);
        $this->assertEquals(90.0, $result->percentage);
        $this->assertEquals('A+', $result->grade);
        $this->assertEquals('Custom teacher remark here', $result->remarks);
        $this->assertStringNotContainsString('Qualified For Second Round', $result->remarks);
    }

    /**
     * Test result without manual remarks stays empty (not auto-filled with Qualified/Not Qualified).
     */
    public function test_admin_can_store_result_without_remarks_remains_null(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->catGalaxy->id,
            'examination_id' => $this->exam->id,
            'name' => 'Galaxy Candidate',
            'gender' => 'Female',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '9999999999',
            'status' => 'Hall Ticket Issued',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.results.store'), [
                'student_id' => $student->id,
                'marks_obtained' => 45, // 45/60 = 75% -> should be A for Galaxy (>= 70%)
                'max_marks' => 60,
                'remarks' => '',
            ]);

        $response->assertRedirect(route('admin.results.index'));

        $result = StudentResult::where('student_id', $student->id)->first();
        $this->assertNotNull($result);
        $this->assertEquals('A', $result->grade);
        $this->assertNull($result->remarks);
    }

    /**
     * Test updating result allows modifying manual remarks freely.
     */
    public function test_admin_can_update_result_remarks(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->catRainbow->id,
            'examination_id' => $this->exam->id,
            'name' => 'Candidate',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '9999999999',
            'status' => 'Hall Ticket Issued',
        ]);

        $result = StudentResult::create([
            'student_id' => $student->id,
            'examination_id' => $this->exam->id,
            'marks_obtained' => 28,
            'max_marks' => 40,
            'percentage' => 70.0,
            'grade' => 'B+',
            'status' => 'Pass',
            'remarks' => 'Initial comment',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('admin.results.update', $result), [
                'marks_obtained' => 32, // 32/40 = 80% -> should be A for Rainbow
                'max_marks' => 40,
                'remarks' => 'Updated remark by examiner',
            ]);

        $response->assertRedirect(route('admin.results.index'));

        $result->refresh();
        $this->assertEquals(32, $result->marks_obtained);
        $this->assertEquals('A', $result->grade);
        $this->assertEquals('Updated remark by examiner', $result->remarks);
    }

    /**
     * Test Planets (Max: 50) and Galaxy (Max: 60) category grading thresholds.
     */
    public function test_category_based_grading_thresholds(): void
    {
        // Rainbow: Max 40, >=90% A+, >=80% A, >=70% B+, >=60% B
        $this->assertEquals(40, StudentResult::getDefaultMaxMarks('RAINBOW 3'));
        $this->assertEquals(40, StudentResult::getDefaultMaxMarks('RAINBOW 5'));
        $this->assertEquals('A+', StudentResult::calculateGrade(90.0, 'RAINBOW 3'));
        $this->assertEquals('A', StudentResult::calculateGrade(80.0, 'RAINBOW 3'));
        $this->assertEquals('B+', StudentResult::calculateGrade(70.0, 'RAINBOW 3'));
        $this->assertEquals('B', StudentResult::calculateGrade(60.0, 'RAINBOW 3'));
        $this->assertNull(StudentResult::calculateGrade(59.9, 'RAINBOW 3'));

        // Planets: Max 50, >=90% A+, >=80% A, >=70% B+, >=60% B
        $this->assertEquals(50, StudentResult::getDefaultMaxMarks('PLANET'));
        $this->assertEquals(50, StudentResult::getDefaultMaxMarks('PLANETS'));
        $this->assertEquals('A+', StudentResult::calculateGrade(90.0, 'PLANET'));
        $this->assertEquals('A', StudentResult::calculateGrade(80.0, 'PLANET'));
        $this->assertEquals('B+', StudentResult::calculateGrade(70.0, 'PLANET'));
        $this->assertEquals('B', StudentResult::calculateGrade(60.0, 'PLANET'));
        $this->assertNull(StudentResult::calculateGrade(59.9, 'PLANET'));

        // Galaxy: Max 60, >=85% A+, >=70% A, >=55% B+, >=40% B
        $this->assertEquals(60, StudentResult::getDefaultMaxMarks('GALAXY HS'));
        $this->assertEquals(60, StudentResult::getDefaultMaxMarks('GALAXY HSS (ARTS)'));
        $this->assertEquals(60, StudentResult::getDefaultMaxMarks('GALAXY HSS (SCIENCE)'));
        $this->assertEquals('A+', StudentResult::calculateGrade(85.0, 'GALAXY HS'));
        $this->assertEquals('A', StudentResult::calculateGrade(70.0, 'GALAXY HS'));
        $this->assertEquals('B+', StudentResult::calculateGrade(55.0, 'GALAXY HSS (ARTS)'));
        $this->assertEquals('B', StudentResult::calculateGrade(40.0, 'GALAXY HSS (SCIENCE)'));
        $this->assertNull(StudentResult::calculateGrade(39.9, 'GALAXY HS'));
    }

    /**
     * Test template export contains both data entry sheet and grading rules sheet.
     */
    public function test_result_template_export_structure(): void
    {
        $export = new \App\Exports\ResultTemplateExport($this->exam->id);
        $sheets = $export->sheets();

        $this->assertCount(2, $sheets);
        $this->assertEquals('Marks Entry Template', $sheets[0]->title());
        $this->assertEquals('Category & Grading Rules', $sheets[1]->title());

        $headings = $sheets[0]->headings();
        $this->assertContains('Registration Number', $headings);
        $this->assertContains('Marks Obtained', $headings);
        $this->assertContains('Max Marks', $headings);
        $this->assertContains('Remarks', $headings);
    }
}
