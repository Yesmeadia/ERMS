<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            $table->string('profile_image')->nullable()->after('email');
            $table->string('two_factor_secret')->nullable()->after('remember_token');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->integer('failed_login_attempts')->default(0)->after('two_factor_enabled');
            $table->timestamp('lockout_until')->nullable()->after('failed_login_attempts');
            $table->boolean('is_active')->default(true)->after('lockout_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn([
                'school_id',
                'profile_image',
                'two_factor_secret',
                'two_factor_enabled',
                'failed_login_attempts',
                'lockout_until',
                'is_active',
            ]);
        });
    }
};
