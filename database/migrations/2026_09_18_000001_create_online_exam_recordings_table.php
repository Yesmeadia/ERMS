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
        Schema::create('online_exam_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_session_id')->constrained('online_exam_sessions')->cascadeOnDelete();
            $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index')->default(0);
            $table->string('storage_disk', 32)->default('local');
            $table->string('storage_path', 255);
            $table->string('session_hash_dir', 128);
            $table->string('sha256_hash', 64);
            $table->unsignedInteger('file_size_bytes')->default(0);
            $table->string('mime_type', 64)->default('video/webm');
            $table->decimal('duration_seconds', 8, 2)->default(0.00);
            $table->boolean('is_final')->default(false);
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->index(['online_exam_session_id', 'chunk_index'], 'oer_sess_chunk_idx');
            $table->index(['online_exam_id', 'created_at'], 'oer_exam_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_recordings');
    }
};
