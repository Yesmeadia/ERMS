<?php

namespace Tests\Feature;

use App\Jobs\GenerateResultPdfPart;
use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\ResultBatch;
use App\Models\ResultPdfPart;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\User;
use App\Services\ResultReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResultBatchGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected School $schoolB;
    protected User $schoolAdmin;
    protected User $schoolAdminB;
    protected User $superAdmin;
    protected Examination $examination;
    protected ClassMaster $class;
    protected CategoryMaster $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Spatie Roles
        Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // Create Masters
        $this->class = ClassMaster::create(['name' => 'Class 10', 'status' => true]);
        $this->category = CategoryMaster::create(['name' => 'General', 'code' => 'GEN', 'status' => true]);
        $this->examination = Examination::create([
            'name' => 'Talent Search Exam 2026',
            'academic_year' => '2026-2027',
            'registration_start_date' => '2026-01-01',
            'registration_end_date' => '2026-08-15',
            'hall_ticket_release_date' => '2026-08-20',
            'status' => 'result published',
        ]);

        // Create Schools
        $this->school = School::create([
            'name' => 'Greenwood International',
            'code' => 'GW101',
            'address' => '123 Forest Avenue, City',
            'zone' => 'North Zone',
            'state' => 'State Name',
            'contact_person' => 'Principal John',
            'mobile_number' => '9876543210',
            'email' => 'gw@example.com',
            'status' => true,
            'is_centre' => true,
        ]);

        $this->schoolB = School::create([
            'name' => 'Starlight Academy',
            'code' => 'SA202',
            'address' => '456 Cosmic Blvd, City',
            'zone' => 'South Zone',
            'state' => 'State Name',
            'contact_person' => 'Principal Jane',
            'mobile_number' => '9876543211',
            'email' => 'sa@example.com',
            'status' => true,
            'is_centre' => true,
        ]);

        // Create Users
        $this->schoolAdmin = User::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'admin@greenwood.com',
        ]);
        $this->schoolAdmin->assignRole('school-admin');

        $this->schoolAdminB = User::factory()->create([
            'school_id' => $this->schoolB->id,
            'email' => 'admin@starlight.com',
        ]);
        $this->schoolAdminB->assignRole('school-admin');

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@yesgenius.com',
        ]);
        $this->superAdmin->assignRole('super-admin');

        Storage::fake('local');
    }

    protected function createStudentsWithResults(int $count, School $school, string $resultStatus = 'Pass'): array
    {
        $students = [];
        for ($i = 1; $i <= $count; $i++) {
            $student = Student::create([
                'examination_id' => $this->examination->id,
                'school_id' => $school->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'centre_id' => $school->id,
                'name' => "Candidate {$school->code} {$i}",
                'dob' => '2010-05-15',
                'father_name' => "Father {$i}",
                'mother_name' => "Mother {$i}",
                'mobile_number' => '9876543210',
                'registration_number' => "REG-{$school->code}-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'hall_ticket_number' => "HT-{$school->code}-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'gender' => ($i % 2 === 0) ? 'female' : 'male',
                'status' => 'Hall Ticket Issued',
                'hall_ticket_issued_at' => now(),
            ]);

            StudentResult::create([
                'student_id' => $student->id,
                'examination_id' => $this->examination->id,
                'marks_obtained' => 85,
                'max_marks' => 100,
                'percentage' => 85.0,
                'grade' => 'A+',
                'status' => $resultStatus,
            ]);

            $students[] = $student;
        }

        return $students;
    }

    public function test_service_creates_batch_and_dispatches_jobs_for_super_admin(): void
    {
        Queue::fake();

        $this->createStudentsWithResults(5, $this->school, 'Pass');
        $this->createStudentsWithResults(5, $this->schoolB, 'Fail');

        $service = app(ResultReportService::class);
        $batch = $service->createAndDispatchBatch(
            'admin',
            null,
            $this->examination->id,
            $this->superAdmin->id,
            ['examination_id' => $this->examination->id]
        );

        $this->assertDatabaseHas('result_batches', [
            'id' => $batch->id,
            'scope' => 'admin',
            'total_students' => 10,
            'total_parts' => 1,
            'status' => 'pending',
        ]);

        $this->assertEquals(10, $batch->stats['total']);
        $this->assertEquals(5, $batch->stats['passed']);
        $this->assertEquals(5, $batch->stats['failed']);

        Queue::assertPushed(GenerateResultPdfPart::class, 1);
    }

    public function test_service_creates_school_scoped_batch(): void
    {
        Queue::fake();

        $this->createStudentsWithResults(4, $this->school, 'Pass');
        $this->createStudentsWithResults(6, $this->schoolB, 'Pass');

        $service = app(ResultReportService::class);
        $batch = $service->createAndDispatchBatch(
            'school',
            $this->school->id,
            $this->examination->id,
            $this->schoolAdmin->id,
            ['examination_id' => $this->examination->id]
        );

        $this->assertDatabaseHas('result_batches', [
            'id' => $batch->id,
            'scope' => 'school',
            'school_id' => $this->school->id,
            'total_students' => 4,
        ]);
    }

    public function test_job_generates_pdf_and_updates_part_status(): void
    {
        $this->createStudentsWithResults(3, $this->school, 'Pass');

        $service = app(ResultReportService::class);
        $batch = $service->createAndDispatchBatch(
            'admin',
            null,
            $this->examination->id,
            $this->superAdmin->id
        );

        $part = $batch->parts->first();
        $job = new GenerateResultPdfPart($part->id);
        $job->handle($service);

        $part->refresh();
        $this->assertEquals('completed', $part->status);
        $this->assertEquals(3, $part->completed_students);
        $this->assertNotEmpty($part->pdf_path);
        $this->assertGreaterThan(0, $part->file_size);

        Storage::disk('local')->assertExists($part->pdf_path);

        $batch->refresh();
        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(1, $batch->completed_parts);
    }

    public function test_super_admin_can_download_completed_part(): void
    {
        $this->createStudentsWithResults(2, $this->school, 'Pass');

        $service = app(ResultReportService::class);
        $batch = $service->createAndDispatchBatch(
            'admin',
            null,
            $this->examination->id,
            $this->superAdmin->id
        );

        $part = $batch->parts->first();
        (new GenerateResultPdfPart($part->id))->handle($service);
        $part->refresh();

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.results.parts.download', $part));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_school_admin_cannot_download_other_school_part(): void
    {
        $this->createStudentsWithResults(2, $this->school, 'Pass');

        $service = app(ResultReportService::class);
        $batch = $service->createAndDispatchBatch(
            'school',
            $this->school->id,
            $this->examination->id,
            $this->schoolAdmin->id
        );

        $part = $batch->parts->first();
        (new GenerateResultPdfPart($part->id))->handle($service);
        $part->refresh();

        // School Admin B tries to download School A's part
        $response = $this->actingAs($this->schoolAdminB)
            ->get(route('school.results.parts.download', $part));

        $response->assertForbidden();
    }

    public function test_batch_status_endpoint_returns_json(): void
    {
        $this->createStudentsWithResults(2, $this->school, 'Pass');

        $service = app(ResultReportService::class);
        $batch = $service->createAndDispatchBatch(
            'admin',
            null,
            $this->examination->id,
            $this->superAdmin->id
        );

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('admin.results.batches.status', $batch));

        $response->assertOk();
        $response->assertJsonStructure([
            'batch_id',
            'batch_uuid',
            'status',
            'total_students',
            'completed_students',
            'progress',
            'parts',
        ]);
    }
}
