<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add mfa_recovery_codes column to users table.
     * Stores JSON-encoded list of one-time backup codes for MFA recovery.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('mfa_recovery_codes')->nullable()->after('two_factor_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mfa_recovery_codes');
        });
    }
};
