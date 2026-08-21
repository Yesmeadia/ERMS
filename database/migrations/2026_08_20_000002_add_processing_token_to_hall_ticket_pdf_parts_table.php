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
        if (Schema::hasTable('hall_ticket_pdf_parts') && !Schema::hasColumn('hall_ticket_pdf_parts', 'processing_token')) {
            Schema::table('hall_ticket_pdf_parts', function (Blueprint $table) {
                $table->string('processing_token', 64)->nullable()->after('completed_at');
                $table->index('processing_token', 'ht_parts_processing_token_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hall_ticket_pdf_parts') && Schema::hasColumn('hall_ticket_pdf_parts', 'processing_token')) {
            Schema::table('hall_ticket_pdf_parts', function (Blueprint $table) {
                $table->dropIndex('ht_parts_processing_token_idx');
                $table->dropColumn('processing_token');
            });
        }
    }
};
