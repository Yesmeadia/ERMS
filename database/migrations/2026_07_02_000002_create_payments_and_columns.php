<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('Card');
            $table->string('status')->default('Pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('payment_status')->default('Unpaid')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
        Schema::dropIfExists('payment_student');
        Schema::dropIfExists('payments');
    }
};
