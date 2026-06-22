<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('examination_id')->constrained('examinations')->cascadeOnDelete();
            
            // Personal Details
            $table->string('name');
            $table->string('gender');
            $table->date('dob');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('mobile_number');
            
            // Academic Details
            $table->string('admission_number');
            $table->string('registration_number')->nullable()->unique();
            
            // Registration Details
            $table->string('status')->default('Draft'); // Draft, Submitted, Under Review, Approved, Rejected, Hall Ticket Issued
            $table->text('remarks')->nullable();
            $table->string('photograph')->nullable(); // Optional path to photo upload

            // Hall Ticket Details
            $table->string('hall_ticket_number')->nullable()->unique();
            $table->timestamp('hall_ticket_issued_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
