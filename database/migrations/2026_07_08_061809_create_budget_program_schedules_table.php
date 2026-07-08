<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_program_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('budget_program_id')->constrained('budget_programs')->cascadeOnDelete();
            $table->unsignedSmallInteger('termin');
            $table->date('estimated_date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('budget_program_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_program_schedules');
    }
};
