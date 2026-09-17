<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the audit table for failed student exam login attempts and
     * a per-session recording quota tracker column.
     */
    public function up(): void
    {
        // 1. Persistent failed student login attempt log (for forensic analysis and brute-force detection)
        if (!Schema::hasTable('online_exam_failed_logins')) {
            Schema::create('online_exam_failed_logins', function (Blueprint $table) {
                $table->id();
                $table->string('registration_number', 64)->index();
                $table->string('failure_reason', 255);
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 512)->nullable();
                $table->string('dob_attempted', 20)->nullable(); // stored as raw string, not actual date
                $table->timestamp('attempted_at')->useCurrent();

                $table->index(['registration_number', 'attempted_at']);
                $table->index('ip_address');
            });
        }

        // 2. Track cumulative recording bytes per session to enforce storage quota
        if (!Schema::hasColumn('online_exam_sessions', 'total_recording_bytes')) {
            Schema::table('online_exam_sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('total_recording_bytes')->default(0)->comment('Cumulative video chunk bytes uploaded for this session.');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_failed_logins');

        Schema::table('online_exam_sessions', function (Blueprint $table) {
            $table->dropColumn('total_recording_bytes');
        });
    }
};
