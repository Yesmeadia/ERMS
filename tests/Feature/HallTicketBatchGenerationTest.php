<?php

namespace Tests\Feature;

use App\Jobs\GenerateHallTicketPdfPart;
use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\HallTicketBatch;
use App\Models\HallTicketPdfPart;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\HallTicketBatchService;
use App\Services\HallTicketPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HallTicketBatchGenerationTest extends TestCase
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
            'status' => 'Registration Started',
        ]);

        // Create Schools with all required schema attributes
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
        config(['hallticket.max_per_pdf' => 100]);
    }

    /**
     * Helper to create N students with 'Hall Ticket Issued' status.
     */
    protected function createIssuedStudents(int $count, ?School $school = null): array
    {
        $school = $school ?? $this->school;
        $studentIds = [];

        for ($i = 1; $i <= $count; $i++) {
            $student = Student::create([
                'school_id' => $school->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'examination_id' => $this->examination->id,
                'centre_id' => $school->id,
                'name' => "Student {$i} - {$school->code}",
                'gender' => 'Male',
                'dob' => '2010-05-15',
                'father_name' => "Father {$i}",
                'mother_name' => "Mother {$i}",
                'mobile_number' => '9876543210',
                'registration_number' => sprintf('%s%04d', $school->code, $i),
                'hall_ticket_number' => sprintf('HT%s%04d', $school->code, $i),
                'status' => 'Hall Ticket Issued',
                'hall_ticket_issued_at' => now(),
            ]);

            $studentIds[] = $student->id;
        }

        return $studentIds;
    }

    /**
     * Test Chunking logic for 10, 50, 100, 101, 150, 200, 201, 250, and 2036 students.
     */
    public function test_chunking_formula_strictly_enforces_maximum_100_per_part(): void
    {
        Queue::fake();
        config(['hallticket.max_per_pdf' => 100]);
        $service = app(HallTicketBatchService::class);

        $testCases = [
            10 => ['expectedParts' => 1, 'partsSizes' => [10]],
            50 => ['expectedParts' => 1, 'partsSizes' => [50]],
            100 => ['expectedParts' => 1, 'partsSizes' => [100]],
            101 => ['expectedParts' => 2, 'partsSizes' => [100, 1]],
            150 => ['expectedParts' => 2, 'partsSizes' => [100, 50]],
            200 => ['expectedParts' => 2, 'partsSizes' => [100, 100]],
            201 => ['expectedParts' => 3, 'partsSizes' => [100, 100, 1]],
            250 => ['expectedParts' => 3, 'partsSizes' => [100, 100, 50]],
            2036 => ['expectedParts' => 21, 'partsSizes' => array_merge(array_fill(0, 20, 100), [36])],
        ];

        foreach ($testCases as $count => $expectation) {
            // Create an isolated school for each test count
            $testSchool = School::create([
                'name' => "Test School {$count}",
                'code' => "TS{$count}",
                'address' => "Address {$count}",
                'zone' => 'Zone 1',
                'state' => 'State',
                'contact_person' => 'Principal',
                'mobile_number' => '9876543210',
                'email' => "school{$count}@example.com",
                'status' => true,
                'is_centre' => true,
            ]);

            $this->createIssuedStudents($count, $testSchool);

            $batch = $service->createAndDispatchBatch(
                $testSchool->id,
                $this->examination->id,
                $this->schoolAdmin->id
            );

            $this->assertEquals($count, $batch->total_students, "Failed total_students for count {$count}");
            $this->assertEquals($expectation['expectedParts'], $batch->total_parts, "Failed total_parts for count {$count}");
            $this->assertCount($expectation['expectedParts'], $batch->parts, "Failed parts count for count {$count}");

            $actualSizes = $batch->parts->pluck('total_students')->all();
            $this->assertEquals($expectation['partsSizes'], $actualSizes, "Failed parts sizes for count {$count}");

            // Verify that no individual part exceeds 100
            foreach ($batch->parts as $part) {
                $this->assertLessThanOrEqual(100, $part->total_students, "Part exceeds maximum 100 students for count {$count}");
            }
        }
    }

    /**
     * Test HTTP request bulk download creation and redirection to batch progress.
     */
    public function test_school_admin_can_request_bulk_download_and_redirects_to_batch(): void
    {
        Queue::fake();
        $this->createIssuedStudents(25);

        $response = $this->actingAs($this->schoolAdmin)
            ->post(route('school.hall-tickets.download-bulk'), [
                'examination_id' => $this->examination->id,
            ]);

        $batch = HallTicketBatch::where('school_id', $this->school->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals(25, $batch->total_students);
        $this->assertEquals(1, $batch->total_parts);

        $response->assertRedirect(route('school.hall-tickets.batches.show', $batch));
        $response->assertSessionHas('success');

        Queue::assertPushed(GenerateHallTicketPdfPart::class, 1);
        Queue::assertPushedOn('pdf', GenerateHallTicketPdfPart::class);
    }

    /**
     * Test that batch creation is idempotent when duplicate requests occur concurrently.
     */
    public function test_batch_creation_prevents_duplicate_batches(): void
    {
        Queue::fake();
        $this->createIssuedStudents(30);

        $service = app(HallTicketBatchService::class);

        $batch1 = $service->createAndDispatchBatch($this->school->id, $this->examination->id, $this->schoolAdmin->id);
        $batch2 = $service->createAndDispatchBatch($this->school->id, $this->examination->id, $this->schoolAdmin->id);

        $this->assertEquals($batch1->id, $batch2->id);
        $this->assertEquals(1, HallTicketBatch::where('school_id', $this->school->id)->count());
    }

    /**
     * Test Job Execution and PDF Generation saves to storage and updates progress.
     */
    public function test_job_execution_generates_pdf_and_updates_batch_progress(): void
    {
        $this->createIssuedStudents(15);
        $service = app(HallTicketBatchService::class);
        $pdfService = app(HallTicketPdfService::class);

        $batch = $service->createAndDispatchBatch($this->school->id, $this->examination->id, $this->schoolAdmin->id);
        $part = $batch->parts->first();

        // Run the queued job synchronously with injected HallTicketPdfService
        $job = new GenerateHallTicketPdfPart($part->id);
        $job->handle($pdfService);

        $part->refresh();
        $batch->refresh();

        $this->assertEquals('completed', $part->status);
        $this->assertEquals(15, $part->completed_students);
        $this->assertNotNull($part->pdf_path);
        $this->assertGreaterThan(0, $part->file_size);

        Storage::disk('local')->assertExists($part->pdf_path);

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(15, $batch->completed_students);
        $this->assertEquals(1, $batch->completed_parts);
        $this->assertEquals(100, $batch->progress_percentage);
    }

    /**
     * Test Status API endpoint for polling frontend.
     */
    public function test_status_api_endpoint_returns_json_progress(): void
    {
        $this->createIssuedStudents(10);
        $service = app(HallTicketBatchService::class);

        $batch = $service->createAndDispatchBatch($this->school->id, $this->examination->id, $this->schoolAdmin->id);

        $response = $this->actingAs($this->schoolAdmin)
            ->getJson(route('school.hall-tickets.batches.status', $batch));

        $response->assertOk();
        $response->assertJsonStructure([
            'batch_id',
            'status',
            'total_students',
            'completed_students',
            'failed_students',
            'total_parts',
            'completed_parts',
            'progress',
            'download_available',
            'parts' => [
                '*' => ['id', 'part_number', 'total_students', 'completed_students', 'status', 'download_url']
            ],
        ]);
    }

    /**
     * Test Security: School Admin A cannot access or download School B's batch, parts, or single student.
     */
    public function test_school_admin_cannot_access_other_schools_batch_or_download_parts(): void
    {
        $this->createIssuedStudents(10, $this->school);
        $this->createIssuedStudents(10, $this->schoolB);

        $service = app(HallTicketBatchService::class);
        $pdfService = app(HallTicketPdfService::class);

        $batchSchoolA = $service->createAndDispatchBatch($this->school->id, $this->examination->id, $this->schoolAdmin->id);
        $partSchoolA = $batchSchoolA->parts->first();

        // Run job to complete part
        (new GenerateHallTicketPdfPart($partSchoolA->id))->handle($pdfService);

        // School Admin B attempting to view School A's batch progress page -> 403
        $this->actingAs($this->schoolAdminB)
            ->get(route('school.hall-tickets.batches.show', $batchSchoolA))
            ->assertForbidden();

        // School Admin B attempting to poll School A's batch status -> 403
        $this->actingAs($this->schoolAdminB)
            ->getJson(route('school.hall-tickets.batches.status', $batchSchoolA))
            ->assertForbidden();

        // School Admin B attempting to download School A's PDF part -> 403
        $this->actingAs($this->schoolAdminB)
            ->get(route('school.hall-tickets.parts.download', $partSchoolA))
            ->assertForbidden();

        // Super Admin CAN access and download School A's part
        $this->actingAs($this->superAdmin)
            ->get(route('admin.hall-tickets.parts.download', $partSchoolA))
            ->assertOk();
    }

    /**
     * Test Retry failed parts workflow with atomic dispatch.
     */
    public function test_retry_failed_parts_requeues_job_and_recalculates(): void
    {
        Queue::fake();
        $this->createIssuedStudents(10);

        $service = app(HallTicketBatchService::class);
        $batch = $service->createAndDispatchBatch($this->school->id, $this->examination->id, $this->schoolAdmin->id);
        $part = $batch->parts->first();

        // Manually set part to failed
        $part->update(['status' => 'failed', 'error_message' => 'Simulated render failure']);
        $batch->recalculateProgress();

        $this->assertEquals(1, $batch->failed_parts);

        $this->actingAs($this->schoolAdmin)
            ->post(route('school.hall-tickets.batches.retry', $batch))
            ->assertRedirect();

        $part->refresh();
        $this->assertEquals('pending', $part->status);
        $this->assertNull($part->error_message);

        Queue::assertPushedOn('pdf', GenerateHallTicketPdfPart::class);
    }

    /**
     * Test Cleanup Command prunes expired batch files from disk and old records.
     */
    public function test_cleanup_command_removes_expired_pdf_files_and_marks_expired(): void
    {
        $this->createIssuedStudents(10);
        $service = app(HallTicketBatchService::class);
        $pdfService = app(HallTicketPdfService::class);

        $batch = $service->createAndDispatchBatch($this->school->id, $this->examination->id, $this->schoolAdmin->id);
        $part = $batch->parts->first();

        // Generate the PDF
        (new GenerateHallTicketPdfPart($part->id))->handle($pdfService);
        $part->refresh();

        Storage::disk('local')->assertExists($part->pdf_path);

        // Make batch expired
        $batch->update(['expires_at' => now()->subHours(25)]);

        $this->artisan('halltickets:cleanup')
            ->assertSuccessful();

        // File should be deleted
        Storage::disk('local')->assertMissing($part->pdf_path);

        $batch->refresh();
        $this->assertEquals('expired', $batch->status);
    }

    /**
     * Test Single Hall Ticket authorization and printing.
     */
    public function test_single_hall_ticket_download_and_print_remain_functional_and_authorized(): void
    {
        $this->createIssuedStudents(1, $this->school);
        $studentA = Student::where('school_id', $this->school->id)->first();

        // Single print (Super Admin) -> OK
        $this->actingAs($this->superAdmin)
            ->get(route('admin.hall-tickets.print-single', $studentA))
            ->assertOk();

        // Single download (School Admin of School A) -> OK
        $this->actingAs($this->schoolAdmin)
            ->get(route('school.hall-tickets.download-single', $studentA))
            ->assertOk();

        // Single download (School Admin of School B accessing Student A) -> 403 Forbidden
        $this->actingAs($this->schoolAdminB)
            ->get(route('school.hall-tickets.download-single', $studentA))
            ->assertForbidden();
    }
}
