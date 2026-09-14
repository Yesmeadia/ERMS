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
        Schema::dropIfExists('online_exam_webrtc_signals');

        Schema::create('online_exam_webrtc_signals', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('online_exam_session_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('sender_type', 16); // 'admin' or 'student'
            $table->string('recipient_type', 16); // 'admin' or 'student'
            $table->string('type', 32); // 'offer', 'answer', 'candidate', 'close', 'request_stream'
            $table->longText('payload'); // JSON payload (sdp or ice candidate)
            $table->boolean('is_consumed')->default(false);
            $table->timestamps();

            // High-performance indexing for rapid signal polling & cleanup
            $table->index(['online_exam_session_id', 'recipient_type', 'is_consumed'], 'webrtc_signals_polling_idx');
            $table->index('created_at', 'webrtc_signals_created_idx');
        });

        // Attempt to add foreign key constraints safely without blocking migration on engine/collation divergence
        try {
            Schema::table('online_exam_webrtc_signals', function (Blueprint $table) {
                if (Schema::hasTable('online_exam_sessions')) {
                    $table->foreign('online_exam_session_id', 'fk_webrtc_session')
                          ->references('id')
                          ->on('online_exam_sessions')
                          ->cascadeOnDelete();
                }
            });
        } catch (\Throwable $e) {
            // Foreign key skipped if database collation or engine differs
        }

        try {
            Schema::table('online_exam_webrtc_signals', function (Blueprint $table) {
                if (Schema::hasTable('users')) {
                    $table->foreign('admin_id', 'fk_webrtc_admin')
                          ->references('id')
                          ->on('users')
                          ->cascadeOnDelete();
                }
            });
        } catch (\Throwable $e) {
            // Foreign key skipped if database collation or engine differs
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_webrtc_signals');
    }
};
