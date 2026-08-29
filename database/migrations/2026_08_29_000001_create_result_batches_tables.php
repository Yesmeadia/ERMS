<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('result_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid')->unique();
            $table->string('scope', 20)->default('admin'); // 'admin' or 'school'
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('examination_id')->nullable()->constrained('examinations')->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->default('STUDENT EXAMINATION RESULT STATEMENT');
            $table->string('subtitle')->nullable();
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('total_parts')->default(0);
            $table->unsignedInteger('completed_parts')->default(0);
            $table->unsignedInteger('failed_parts')->default(0);
            $table->unsignedInteger('completed_students')->default(0);
            $table->unsignedInteger('failed_students')->default(0);
            $table->string('status', 30)->default('pending'); // pending, processing, completed, completed_with_errors, failed, expired
            $table->json('filter_criteria')->nullable();
            $table->json('stats')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'examination_id', 'status'], 'rb_school_exam_status_idx');
            $table->index(['status', 'expires_at'], 'rb_status_expires_idx');
            $table->index('requested_by', 'rb_requested_by_idx');
        });

        Schema::create('result_pdf_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('result_batches')->cascadeOnDelete();
            $table->unsignedInteger('part_number');
            $table->json('student_ids');
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('completed_students')->default(0);
            $table->string('status', 30)->default('pending'); // pending, processing, completed, failed
            $table->string('processing_token', 64)->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'part_number'], 'rb_parts_batch_part_unique');
            $table->index(['batch_id', 'status'], 'rb_parts_batch_status_idx');
            $table->index('processing_token', 'rb_parts_processing_token_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_pdf_parts');
        Schema::dropIfExists('result_batches');
    }
};
