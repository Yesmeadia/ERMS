<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('examinations')
            ->where('status', 'Ongoing')
            ->update(['status' => 'Examination Ongoing']);

        DB::table('examinations')
            ->where('status', 'Open')
            ->update(['status' => 'Registration Started']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('examinations')
            ->where('status', 'Examination Ongoing')
            ->update(['status' => 'Ongoing']);

        DB::table('examinations')
            ->where('status', 'Registration Started')
            ->update(['status' => 'Open']);
    }
};
