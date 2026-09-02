<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        // Backfill last_login_at from activity_log if available
        if (Schema::hasTable('activity_log')) {
            try {
                $logs = DB::table('activity_log')
                    ->where('description', 'like', '%logged in%')
                    ->select('causer_id', DB::raw('MAX(created_at) as last_login'))
                    ->groupBy('causer_id')
                    ->get();

                foreach ($logs as $log) {
                    if ($log->causer_id && $log->last_login) {
                        DB::table('users')
                            ->where('id', $log->causer_id)
                            ->update(['last_login_at' => $log->last_login]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore any backfill exceptions
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login_at');
        });
    }
};
