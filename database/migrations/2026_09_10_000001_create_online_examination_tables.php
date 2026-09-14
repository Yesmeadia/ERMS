<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the Online Examination module.
     */
    public function up(): void
    {
        // 1. Online Exams Table
        Schema::create('online_exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 64)->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('default_question_time_limit')->default(60); // seconds
            $table->decimal('total_marks', 6, 2)->default(0.00);
            $table->decimal('pass_marks', 6, 2)->default(0.00);

            // Rules & Security Controls
            $table->boolean('enable_camera')->default(true);
            $table->boolean('enable_fullscreen')->default(true);
            $table->unsignedSmallInteger('max_fullscreen_violations')->default(3);
            $table->boolean('allow_previous_question')->default(false);
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_options')->default(true);

            // Result & Feedback Visibility
            $table->boolean('show_result_immediately')->default(true);
            $table->boolean('show_correct_answers')->default(false);
            $table->boolean('show_question_marks')->default(true);
            $table->boolean('show_rank')->default(false);

            // Speed Bonus Settings (Disabled by default in V1)
            $table->boolean('enable_speed_bonus')->default(false);
            $table->string('speed_bonus_formula', 32)->default('linear'); // linear, tier, percentage
            $table->decimal('max_bonus_per_question', 4, 2)->default(0.50);
            $table->decimal('max_total_bonus', 5, 2)->default(10.00);

            // Capacity Limit
            $table->unsignedSmallInteger('max_eligible_students')->default(199);
            $table->longText('instructions')->nullable();

            // Status & Timestamps
            $table->string('status', 32)->default('DRAFT'); // DRAFT, PUBLISHED, ACTIVE, COMPLETED, RESULT_PUBLISHED, CANCELLED
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'exam_date'], 'oe_status_date_idx');
            $table->index('category_id', 'oe_cat_idx');
        });

        // 2. Online Exam Students (Enrollment & Eligibility - Max 199)
        Schema::create('online_exam_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('registration_number', 32);
            $table->boolean('is_eligible')->default(true);
            $table->string('eligibility_notes')->nullable();
            $table->timestamp('enrolled_at');
            $table->timestamps();

            $table->unique(['online_exam_id', 'student_id'], 'oes_exam_student_uq');
            $table->unique(['online_exam_id', 'registration_number'], 'oes_exam_reg_uq');
            $table->index(['online_exam_id', 'is_eligible'], 'oes_exam_eligible_idx');
        });

        // 3. Question Bank Table
        Schema::create('online_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->string('topic')->nullable();
            $table->string('difficulty', 32)->default('MEDIUM'); // EASY, MEDIUM, HARD
            $table->string('question_type', 32)->default('MCQ'); // MCQ, TRUE_FALSE, MULTIPLE_SELECT, FILL_IN_BLANK
            $table->longText('question_text');
            $table->text('explanation')->nullable();
            $table->decimal('default_marks', 5, 2)->default(1.00);
            $table->decimal('negative_marks', 5, 2)->default(0.00);
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->string('multiple_select_criteria', 32)->default('ALL_CORRECT'); // ALL_CORRECT, PARTIAL_WITH_PENALTY, PARTIAL_NO_PENALTY
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active'], 'oq_cat_active_idx');
            $table->index('question_type', 'oq_type_idx');
        });

        // 4. Question Options
        Schema::create('online_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('online_questions')->cascadeOnDelete();
            $table->string('option_identifier', 8); // A, B, C, D...
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['question_id', 'sort_order'], 'oqo_q_sort_idx');
        });

        // 5. Question Images
        Schema::create('online_question_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('online_questions')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('disk', 32)->default('public');
            $table->string('file_name');
            $table->string('mime_type', 64);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
        });

        // 6. Frozen Exam Questions (Assignment to Exams)
        Schema::create('online_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('online_questions')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->decimal('marks', 5, 2);
            $table->decimal('negative_marks', 5, 2)->default(0.00);
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->timestamps();

            $table->unique(['online_exam_id', 'question_id'], 'oeq_exam_q_uq');
            $table->index(['online_exam_id', 'sort_order'], 'oeq_exam_sort_idx');
        });

        // 7. Online Exam Sessions (Single Active Session Machine)
        Schema::create('online_exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('registration_number', 32);
            $table->string('session_token', 64)->unique();
            $table->unsignedInteger('session_version')->default(1);
            $table->string('device_fingerprint_hash', 64)->nullable();
            $table->string('status', 32)->default('NOT_STARTED'); // NOT_STARTED, READY, IN_PROGRESS, QUESTION_ACTIVE, ANSWERED, QUESTION_TIMEOUT, SUBMITTED, EXPIRED, TERMINATED

            // Randomized order for this specific student's attempt
            $table->json('question_order')->nullable();
            $table->unsignedBigInteger('current_question_id')->nullable();
            $table->unsignedSmallInteger('current_question_index')->default(0);

            // Server-Side High Precision Timestamps
            $table->timestamp('current_question_started_at', 3)->nullable();
            $table->timestamp('current_question_deadline_at', 3)->nullable();
            $table->timestamp('exam_started_at', 3)->nullable();
            $table->timestamp('exam_deadline_at', 3)->nullable();

            // Heartbeat & Locking
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('locked_at')->nullable();

            // Signals & Violations
            $table->string('camera_status', 32)->default('UNKNOWN'); // UNKNOWN, ACTIVE, STOPPED, INTERRUPTED
            $table->boolean('fullscreen_status')->default(false);
            $table->unsignedSmallInteger('violations_count')->default(0);
            $table->string('termination_reason')->nullable();

            // Client Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['online_exam_id', 'student_id'], 'oesess_exam_student_uq');
            $table->index(['online_exam_id', 'status'], 'oesess_exam_status_idx');
            $table->index(['registration_number', 'status'], 'oesess_reg_status_idx');
            $table->index('last_heartbeat_at', 'oesess_heartbeat_idx');
        });

        // 8. Exam Session Events (Anti-Cheating Logs)
        Schema::create('online_exam_session_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_session_id')->constrained('online_exam_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('event_type', 48); // LOGIN, CAMERA_STARTED, FULLSCREEN_EXIT, etc.
            $table->timestamp('event_time', 3);
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['online_exam_session_id', 'event_type'], 'oese_sess_event_idx');
            $table->index(['student_id', 'event_time'], 'oese_stud_time_idx');
        });

        // 9. Submitted & Evaluated Answers (Immutable once submitted)
        Schema::create('online_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_session_id')->constrained('online_exam_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('online_questions')->cascadeOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->text('text_answer')->nullable();
            $table->timestamp('submitted_at', 3)->nullable();
            $table->unsignedInteger('time_spent_milliseconds')->default(0);
            $table->boolean('is_locked')->default(true);

            // Server Evaluated Fields
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_awarded', 5, 2)->default(0.00);
            $table->decimal('negative_marks_deducted', 5, 2)->default(0.00);
            $table->decimal('speed_bonus_awarded', 5, 2)->default(0.00);
            $table->string('evaluation_status', 32)->default('PENDING'); // PENDING, EVALUATED
            $table->timestamps();

            $table->unique(['online_exam_session_id', 'question_id'], 'oea_sess_q_uq');
            $table->index(['student_id', 'question_id'], 'oea_stud_q_idx');
        });

        // 10. Exam Results Table
        Schema::create('online_exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_session_id')->constrained('online_exam_sessions')->cascadeOnDelete();
            $table->foreignId('online_exam_id')->constrained('online_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('registration_number', 32);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('total_attempted')->default(0);
            $table->unsignedInteger('total_correct')->default(0);
            $table->unsignedInteger('total_wrong')->default(0);
            $table->unsignedInteger('total_unanswered')->default(0);
            $table->decimal('objective_marks', 6, 2)->default(0.00);
            $table->decimal('speed_bonus_marks', 6, 2)->default(0.00);
            $table->decimal('negative_marks', 6, 2)->default(0.00);
            $table->decimal('final_score', 6, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->string('grade', 16)->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->string('status', 32)->default('PASS'); // PASS, FAIL
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['online_exam_id', 'student_id'], 'oer_exam_stud_uq');
            $table->index(['online_exam_id', 'final_score'], 'oer_exam_score_idx');
            $table->index('registration_number', 'oer_reg_idx');
        });

        // 11. Administrative Audit Logs
        Schema::create('online_exam_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->string('entity_type', 64);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at'], 'oeal_user_time_idx');
            $table->index(['entity_type', 'entity_id'], 'oeal_entity_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_audit_logs');
        Schema::dropIfExists('online_exam_results');
        Schema::dropIfExists('online_exam_answers');
        Schema::dropIfExists('online_exam_session_events');
        Schema::dropIfExists('online_exam_sessions');
        Schema::dropIfExists('online_exam_questions');
        Schema::dropIfExists('online_question_images');
        Schema::dropIfExists('online_question_options');
        Schema::dropIfExists('online_questions');
        Schema::dropIfExists('online_exam_students');
        Schema::dropIfExists('online_exams');
    }
};
