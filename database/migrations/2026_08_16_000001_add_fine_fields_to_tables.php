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
        // 1. Add is_fine_enabled to schools table
        if (!Schema::hasColumn('schools', 'is_fine_enabled')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('is_fine_enabled')->default(true)->after('is_centre');
            });
        }

        // 2. Add base_amount and fine_amount to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'base_amount')) {
                $table->decimal('base_amount', 10, 2)->default(0.00)->after('amount');
            }
            if (!Schema::hasColumn('payments', 'fine_amount')) {
                $table->decimal('fine_amount', 10, 2)->default(0.00)->after('base_amount');
            }
        });

        // 3. Add base_amount and fine_amount to payment_student pivot table
        Schema::table('payment_student', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_student', 'base_amount')) {
                $table->decimal('base_amount', 10, 2)->default(0.00)->after('amount');
            }
            if (!Schema::hasColumn('payment_student', 'fine_amount')) {
                $table->decimal('fine_amount', 10, 2)->default(0.00)->after('base_amount');
            }
        });

        // 4. Add fine_amount and without_fine_end_date to examinations table
        Schema::table('examinations', function (Blueprint $table) {
            if (!Schema::hasColumn('examinations', 'without_fine_end_date')) {
                $table->dateTime('without_fine_end_date')->nullable()->after('registration_end_date');
            }
            if (!Schema::hasColumn('examinations', 'fine_amount')) {
                $table->decimal('fine_amount', 10, 2)->default(50.00)->after('without_fine_end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'is_fine_enabled')) {
                $table->dropColumn('is_fine_enabled');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'base_amount')) {
                $table->dropColumn('base_amount');
            }
            if (Schema::hasColumn('payments', 'fine_amount')) {
                $table->dropColumn('fine_amount');
            }
        });

        Schema::table('payment_student', function (Blueprint $table) {
            if (Schema::hasColumn('payment_student', 'base_amount')) {
                $table->dropColumn('base_amount');
            }
            if (Schema::hasColumn('payment_student', 'fine_amount')) {
                $table->dropColumn('fine_amount');
            }
        });

        Schema::table('examinations', function (Blueprint $table) {
            if (Schema::hasColumn('examinations', 'without_fine_end_date')) {
                $table->dropColumn('without_fine_end_date');
            }
            if (Schema::hasColumn('examinations', 'fine_amount')) {
                $table->dropColumn('fine_amount');
            }
        });
    }
};
