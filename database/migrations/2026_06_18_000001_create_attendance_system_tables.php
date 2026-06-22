<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('hallticket_no')->unique();
            $table->string('qr_token')->unique();
            $table->date('issue_date');
            $table->string('status')->default('Issued'); // Issued, Revoked
            $table->timestamps();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('examinations')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('attendance_time');
            $table->foreignId('marked_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('Present'); // Present, Absent
            $table->timestamps();

            // Prevent duplicate attendance
            $table->unique(['student_id', 'exam_id', 'attendance_date']);
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('scanner_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scan_time');
            $table->string('device_info')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('action'); // scan_success, scan_duplicate, scan_invalid, mark_present
            $table->timestamps();
        });

        // Seed default roles if they don't exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'invigilator', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('hall_tickets');
    }
};
