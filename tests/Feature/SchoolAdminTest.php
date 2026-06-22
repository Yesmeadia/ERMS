<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\ClassMaster;
use App\Models\CategoryMaster;
use App\Models\Student;
use App\Models\Examination;
use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchoolAdminTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $schoolAdmin;
    protected $anotherSchool;
    protected $anotherSchoolAdmin;
    protected $class;
    protected $category;
    protected $examination;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // Create Masters
        $this->class = ClassMaster::create([
            'name' => 'Class 10',
            'code' => 'C10',
            'description' => '10th Standard',
            'status' => true,
        ]);

        $this->category = CategoryMaster::create([
            'name' => 'General',
            'code' => 'GEN',
            'description' => 'General Category',
            'status' => true,
        ]);

        $this->examination = Examination::create([
            'name' => 'SSLC Examination 2027',
            'academic_year' => '2026-2027',
            'registration_start_date' => '2026-09-01',
            'registration_end_date' => '2026-11-30',
            'hall_ticket_release_date' => '2027-02-15',
            'status' => 'Open',
        ]);

        // Create Schools and Users
        $this->school = School::create([
            'name' => 'School A',
            'code' => 'SCH001',
            'address' => 'Address A',
            'zone' => 'District A',
            'state' => 'State A',
            'contact_person' => 'Person A',
            'mobile_number' => '1234567890',
            'email' => 'schoola@erms.com',
            'status' => true,
        ]);

        $this->schoolAdmin = User::create([
            'name' => 'Admin A',
            'email' => 'admina@erms.com',
            'password' => bcrypt('password'),
            'school_id' => $this->school->id,
        ]);
        $this->schoolAdmin->assignRole($schoolAdminRole);

        $this->anotherSchool = School::create([
            'name' => 'School B',
            'code' => 'SCH002',
            'address' => 'Address B',
            'zone' => 'District B',
            'state' => 'State B',
            'contact_person' => 'Person B',
            'mobile_number' => '0987654321',
            'email' => 'schoolb@erms.com',
            'status' => true,
        ]);

        $this->anotherSchoolAdmin = User::create([
            'name' => 'Admin B',
            'email' => 'adminb@erms.com',
            'password' => bcrypt('password'),
            'school_id' => $this->anotherSchool->id,
        ]);
        $this->anotherSchoolAdmin->assignRole($schoolAdminRole);
    }

    /**
     * Test school admin dashboard displays statistics.
     */
    public function test_school_admin_dashboard_displays_stats_scoped_to_school(): void
    {
        // Add students to School A
        Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student A1',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father A1',
            'mother_name' => 'Mother A1',
            'mobile_number' => '1111111111',
            'status' => 'Draft',
        ]);

        Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student A2',
            'gender' => 'Female',
            'dob' => '10-10-2000',
            'father_name' => 'Father A2',
            'mother_name' => 'Mother A2',
            'mobile_number' => '2222222222',
            'status' => 'Submitted',
        ]);

        // Add student to School B (should not count in School A stats)
        Student::create([
            'school_id' => $this->anotherSchool->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student B1',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father B1',
            'mother_name' => 'Mother B1',
            'mobile_number' => '3333333333',
            'status' => 'Draft',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('School A');
        $response->assertSee('SCH001');

        // Check stats passed to view
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_registered'] === 2 &&
                $stats['submitted'] === 1 &&
                $stats['approved'] === 0 &&
                $stats['rejected'] === 0;
        });
    }

    /**
     * Test student list is scoped to user's school.
     */
    public function test_school_admin_can_view_only_their_school_students(): void
    {
        $studentA = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Visible Student',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father A1',
            'mother_name' => 'Mother A1',
            'mobile_number' => '1111111111',
            'status' => 'Draft',
        ]);

        $studentB = Student::create([
            'school_id' => $this->anotherSchool->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Hidden Student',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father B1',
            'mother_name' => 'Mother B1',
            'mobile_number' => '3333333333',
            'status' => 'Draft',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.students.index'));

        $response->assertStatus(200);
        $response->assertSee('Visible Student');
        $response->assertDontSee('Hidden Student');
    }

    /**
     * Test school admin can register new student.
     */
    public function test_school_admin_can_register_new_student(): void
    {
        $response = $this->actingAs($this->schoolAdmin)
            ->post(route('school.students.store'), [
                'examination_id' => $this->examination->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'name' => 'New Student',
                'gender' => 'Male',
                'dob' => '10-10-2000',
                'father_name' => 'Father Test',
                'mother_name' => 'Mother Test',
                'mobile_number' => '9999999999',
            ]);

        $response->assertRedirect(route('school.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'name' => 'New Student',
            'school_id' => $this->school->id,
            'status' => 'Draft',
        ]);
    }

    /**
     * Test registration fails when examination status is closed.
     */
    public function test_school_admin_cannot_register_student_when_exam_closed(): void
    {
        $this->examination->update(['status' => 'Closed']);

        $response = $this->actingAs($this->schoolAdmin)
            ->from(route('school.students.create'))
            ->post(route('school.students.store'), [
                'examination_id' => $this->examination->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'name' => 'Blocked Student',
                'gender' => 'Male',
                'dob' => '10-10-2000',
                'father_name' => 'Father Test',
                'mother_name' => 'Mother Test',
                'mobile_number' => '9999999999',
            ]);

        $response->assertRedirect(route('school.students.create'));
        $response->assertSessionHasErrors(['examination_id']);
        $this->assertDatabaseMissing('students', [
            'name' => 'Blocked Student',
        ]);
    }

    /**
     * Test school admin can edit draft student.
     */
    public function test_school_admin_can_edit_draft_student(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Original Name',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111'






















            ,
        ,
            'status' => 'Draft',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->put(route('school.students.update', $student), [
                'examination_id' => $this->examination->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'name' => 'Updated Name',
                'gender' => 'Male',
                'dob' => '10-10-2000',
                'father_name' => 'Father',
                'mother_name' => 'Mother',
                'mobile_number' => '1111111111',
            ]);

        $response->assertRedirect(route('school.students.index'));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Updated Name',
            'status' => 'Draft',
        ]);
    }

    /**
     * Test school admin cannot edit another school's student.
     */
    public function test_school_admin_cannot_edit_another_school_student(): void
    {
        $student = Student::create([
            'school_id' => $this->anotherSchool->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'School B Student',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Draft',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->put(route('school.students.update', $student), [
                'examination_id' => $this->examination->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'name' => 'Hacked Name',
                'gender' => 'Male',
                'dob' => '10-10-2000',
                'father_name' => 'Father',
                'mother_name' => 'Mother',
                'mobile_number' => '1111111111',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'School B Student',
        ]);
    }

    /**
     * Test school admin cannot edit submitted/approved student.
     */
    public function test_school_admin_cannot_edit_submitted_student(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Submitted Student',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Submitted',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->put(route('school.students.update', $student), [
                'examination_id' => $this->examination->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'name' => 'Hacked Name',
                'gender' => 'Male',
                'dob' => '10-10-2000',
                'father_name' => 'Father',
                'mother_name' => 'Mother',
                'mobile_number' => '1111111111',
            ]);

        $response->assertRedirect(route('school.students.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Submitted Student',
        ]);
    }

    /**
     * Test delete draft student works.
     */
    public function test_school_admin_can_delete_draft_student(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Delete Me',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Draft',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->delete(route('school.students.destroy', $student));

        $response->assertRedirect(route('school.students.index'));
        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }

    /**
     * Test cannot delete submitted/approved student.
     */
    public function test_school_admin_cannot_delete_submitted_student(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Keep Me',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Submitted',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->delete(route('school.students.destroy', $student));

        $response->assertRedirect(route('school.students.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
        ]);
    }

    /**
     * Test submit student registration.
     */
    public function test_school_admin_can_submit_registration(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Submit Student',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Draft',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->post(route('school.students.submit', $student));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Submitted', $student->status);
        $this->assertNull($student->registration_number);
    }

    /**
     * Test download template.
     */
    public function test_school_admin_can_download_import_template(): void
    {
        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.students.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=erms_student_import_template.xlsx');
    }

    /**
     * Test bulk import.
     */
    public function test_school_admin_can_bulk_import_students(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->schoolAdmin)
            ->post(route('school.students.import'), [
                'import_examination_id' => $this->examination->id,
                'excel_file' => UploadedFile::fake()->create('students.xlsx', 100),
            ]);

        $response->assertRedirect(route('school.students.index'));
        $response->assertSessionHas('success');
        Excel::assertImported('students.xlsx');
    }

    /**
     * Test downloading single hall ticket.
     */
    public function test_school_admin_can_download_issued_hall_ticket(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'HT Candidate',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Hall Ticket Issued',
            'hall_ticket_number' => 'HT-2027-000001',
            'hall_ticket_issued_at' => now(),
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.hall-tickets.download-single', $student));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test cannot download unissued hall ticket.
     */
    public function test_school_admin_cannot_download_unissued_hall_ticket(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Approved Candidate',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.hall-tickets.download-single', $student));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test cannot download other school's hall ticket.
     */
    public function test_school_admin_cannot_download_another_school_hall_ticket(): void
    {
        $student = Student::create([
            'school_id' => $this->anotherSchool->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'School B Candidate',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Hall Ticket Issued',
            'hall_ticket_number' => 'HT-2027-000002',
            'hall_ticket_issued_at' => now(),
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.hall-tickets.download-single', $student));

        $response->assertStatus(403);
    }

    /**
     * Test report dashboard statistics.
     */
    public function test_school_admin_reports_displays_scoped_data(): void
    {
        $studentA = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'School A Registered',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Submitted',
        ]);

        $studentB = Student::create([
            'school_id' => $this->anotherSchool->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'School B Registered',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Submitted',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('school.reports.index', ['type' => 'submitted']));

        $response->assertStatus(200);
        $response->assertSee('School A Registered');
        $response->assertDontSee('School B Registered');
    }

    /**
     * Test report exports format.
     */
    public function test_school_admin_can_export_reports(): void
    {
        Student::create([
            'school_id' => $this->school->id,
            'class_id' => $this->class->id,
            'category_id' => $this->category->id,
            'examination_id' => $this->examination->id,
            'name' => 'Report Student',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Submitted',
        ]);

        // Excel format
        $responseExcel = $this->actingAs($this->schoolAdmin)
            ->get(route('school.reports.export', ['type' => 'submitted', 'format' => 'excel']));
        $responseExcel->assertStatus(200);
        $responseExcel->assertHeader('content-disposition');

        // CSV format
        $responseCsv = $this->actingAs($this->schoolAdmin)
            ->get(route('school.reports.export', ['type' => 'submitted', 'format' => 'csv']));
        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('content-disposition');

        // PDF format
        $responsePdf = $this->actingAs($this->schoolAdmin)
            ->get(route('school.reports.export', ['type' => 'submitted', 'format' => 'pdf']));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test hall ticket generation assigns correct registration number formats based on class and category.
     */
    public function test_hall_ticket_generation_assigns_correct_registration_number_formats(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@erms.com',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Define classes
        $class3 = ClassMaster::create(['name' => 'Class 3rd', 'code' => 'C3', 'status' => true]);
        $class4 = ClassMaster::create(['name' => 'Class 4th', 'code' => 'C4', 'status' => true]);
        $class5 = ClassMaster::create(['name' => 'Class 5th', 'code' => 'C5', 'status' => true]);
        $class6 = ClassMaster::create(['name' => 'Class 6th', 'code' => 'C6', 'status' => true]);
        $class10 = ClassMaster::create(['name' => 'Class 10th', 'code' => 'C10th', 'status' => true]);

        // Define categories
        $catRainbow3 = CategoryMaster::create(['name' => 'Rainbow 3', 'code' => 'R3', 'status' => true]);
        $catRainbow4 = CategoryMaster::create(['name' => 'Rainbow 4', 'code' => 'R4', 'status' => true]);
        $catRainbow5 = CategoryMaster::create(['name' => 'Rainbow 5', 'code' => 'R5', 'status' => true]);
        $catPlanet = CategoryMaster::create(['name' => 'Planet', 'code' => 'PL', 'status' => true]);
        $catGalaxy = CategoryMaster::create(['name' => 'Galaxy HS', 'code' => 'GHS', 'status' => true]);

        // 1. Test Class 3rd (Rainbow 3) -> Random 3xxxx
        $student3 = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $class3->id,
            'category_id' => $catRainbow3->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student 3',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.hall-tickets.generate-single', $student3));
        $response->assertRedirect();

        $student3->refresh();
        $this->assertEquals('Hall Ticket Issued', $student3->status);
        $this->assertNotNull($student3->registration_number);
        $this->assertMatchesRegularExpression('/^3\d{4}$/', $student3->registration_number);

        // 2. Test Class 4th (Rainbow 4) -> Sequential starts at 40001
        $student4 = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $class4->id,
            'category_id' => $catRainbow4->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student 4',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Approved',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.hall-tickets.generate-single', $student4));

        $student4->refresh();
        $this->assertEquals('40001', $student4->registration_number);

        // Another 4th standard student -> should get 40002
        $student4_2 = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $class4->id,
            'category_id' => $catRainbow4->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student 4.2',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Approved',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.hall-tickets.generate-single', $student4_2));

        $student4_2->refresh();
        $this->assertEquals('40002', $student4_2->registration_number);

        // 3. Test Class 5th (Rainbow 5) -> Sequential starts at 50001
        $student5 = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $class5->id,
            'category_id' => $catRainbow5->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student 5',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Approved',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.hall-tickets.generate-single', $student5));

        $student5->refresh();
        $this->assertEquals('50001', $student5->registration_number);

        // 4. Test Planet Category -> Sequential starts at 60001
        $student6 = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $class6->id,
            'category_id' => $catPlanet->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student Planet',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Approved',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.hall-tickets.generate-single', $student6));

        $student6->refresh();
        $this->assertEquals('60001', $student6->registration_number);

        // 5. Test Galaxy Category -> Sequential starts at 90001
        $studentGalaxy = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $class10->id,
            'category_id' => $catGalaxy->id,
            'examination_id' => $this->examination->id,
            'name' => 'Student Galaxy',
            'gender' => 'Male',
            'dob' => '10-10-2000',
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'mobile_number' => '1111111111',
            'status' => 'Approved',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.hall-tickets.generate-single', $studentGalaxy));

        $studentGalaxy->refresh();
        $this->assertEquals('90001', $studentGalaxy->registration_number);
    }
}
