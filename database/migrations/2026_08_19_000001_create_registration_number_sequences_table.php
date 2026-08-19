<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('range_start');
            $table->unsignedBigInteger('range_end');
            $table->unsignedBigInteger('next_number');
            $table->timestamps();

            $table->unique(['range_start', 'range_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_number_sequences');
    }
};
