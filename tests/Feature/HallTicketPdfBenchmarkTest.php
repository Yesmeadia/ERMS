<?php

namespace Tests\Feature;

use App\Jobs\GenerateHallTicketPdfPart;
use App\Models\CategoryMaster;
use App\Models\ClassMaster;
use App\Models\Examination;
use App\Models\HallTicketBatch;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\HallTicketBatchService;
use App\Services\HallTicketPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HallTicketPdfBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $schoolAdmin;
    protected Examination $examination;
    protected ClassMaster $class;
    protected CategoryMaster $category;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        $this->class = ClassMaster::create(['name' => 'Class 10', 'status' => true]);
        $this->category = CategoryMaster::create(['name' => 'General', 'code' => 'GEN', 'status' => true]);
        $this->examination = Examination::create([
            'name' => 'Benchmark Examination 2026',
            'academic_year' => '2026-2027',
            'registration_start_date' => '2026-01-01',
            'registration_end_date' => '2026-08-15',
            'hall_ticket_release_date' => '2026-08-20',
            'status' => 'Registration Started',
        ]);

        $this->school = School::create([
            'name' => 'Benchmark Academy',
            'code' => 'BM100',
            'address' => '789 Test Lane, Metro City',
            'zone' => 'Central Zone',
            'state' => 'State Name',
            'contact_person' => 'Benchmark Admin',
            'mobile_number' => '9998887770',
            'email' => 'bm@example.com',
            'status' => true,
            'is_centre' => true,
        ]);

        $this->schoolAdmin = User::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'admin@bm.com',
        ]);
        $this->schoolAdmin->assignRole('school-admin');

        Storage::fake('local');
        config(['hallticket.max_per_pdf' => 100]);
    }

    protected function createIssuedStudents(int $count): array
    {
        $studentIds = [];
        for ($i = 1; $i <= $count; $i++) {
            $student = Student::create([
                'school_id' => $this->school->id,
                'class_id' => $this->class->id,
                'category_id' => $this->category->id,
                'examination_id' => $this->examination->id,
                'centre_id' => $this->school->id,
                'name' => "Candidate {$i}",
                'gender' => ($i % 2 === 0) ? 'Female' : 'Male',
                'dob' => '2010-01-01',
                'father_name' => "Father {$i}",
                'mother_name' => "Mother {$i}",
                'mobile_number' => '9998887776',
                'registration_number' => sprintf('BM%04d', $i),
                'hall_ticket_number' => sprintf('HTBM%04d', $i),
                'status' => 'Hall Ticket Issued',
                'hall_ticket_issued_at' => now(),
            ]);
            $studentIds[] = $student->id;
        }
        return $studentIds;
    }

    /**
     * Benchmark generation time and peak memory consumption for 10, 50, and 100 students.
     */
    public function test_benchmark_memory_and_time_for_10_50_100_students(): void
    {
        $counts = [10, 50, 100];
        $service = app(HallTicketBatchService::class);
        $pdfService = app(HallTicketPdfService::class);
        $results = [];

        foreach ($counts as $count) {
            $this->createIssuedStudents($count);

            $batch = $service->createAndDispatchBatch(
                $this->school->id,
                $this->examination->id,
                $this->schoolAdmin->id
            );

            $part = $batch->parts->first();

            // Measure memory before
            gc_collect_cycles();
            $timeStart = microtime(true);

            $job = new GenerateHallTicketPdfPart($part->id);
            $job->handle($pdfService);

            $timeEnd = microtime(true);
            $memPeak = memory_get_peak_usage(true);
            gc_collect_cycles();

            $elapsedSeconds = round($timeEnd - $timeStart, 3);
            $peakMemoryMB = round($memPeak / 1024 / 1024, 2);
            $part->refresh();
            $fileSizeBytes = $part->file_size;
            $fileSizeKB = round($fileSizeBytes / 1024, 1);

            $results[$count] = [
                'students' => $count,
                'time_seconds' => $elapsedSeconds,
                'peak_memory_mb' => $peakMemoryMB,
                'pdf_size_kb' => $fileSizeKB,
            ];

            // Assertions
            $this->assertEquals('completed', $part->status);
            $this->assertGreaterThan(0, $fileSizeBytes);
            $this->assertLessThan(256, $peakMemoryMB, "Peak memory exceeded 256MB for {$count} students");

            // Clear students and batches for next run (forceDelete to clear unique constraints)
            Student::withTrashed()->where('school_id', $this->school->id)->forceDelete();
            HallTicketBatch::where('school_id', $this->school->id)->delete();
        }

        // Output results to stdout/log for inspection
        fwrite(STDOUT, "\n" . str_repeat('=', 65) . "\n");
        fwrite(STDOUT, sprintf("%-15s | %-15s | %-15s | %-15s\n", "Student Count", "Time (seconds)", "Peak RAM (MB)", "PDF Size (KB)"));
        fwrite(STDOUT, str_repeat('-', 65) . "\n");
        foreach ($results as $r) {
            fwrite(STDOUT, sprintf("%-15d | %-15.3f | %-15.2f | %-15.1f\n", $r['students'], $r['time_seconds'], $r['peak_memory_mb'], $r['pdf_size_kb']));
        }
        fwrite(STDOUT, str_repeat('=', 65) . "\n");
    }
}
