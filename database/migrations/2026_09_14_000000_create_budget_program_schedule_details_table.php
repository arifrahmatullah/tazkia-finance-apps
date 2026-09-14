<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_program_schedule_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('budget_program_schedule_id');
            $table->uuid('budget_program_detail_id');
            $table->decimal('unit_price', 18, 2);
            $table->timestamps();

            $table->foreign('budget_program_schedule_id', 'bpsd_schedule_id_foreign')
                ->references('id')->on('budget_program_schedules')->cascadeOnDelete();
            $table->foreign('budget_program_detail_id', 'bpsd_detail_id_foreign')
                ->references('id')->on('budget_program_details')->cascadeOnDelete();
            $table->unique(['budget_program_schedule_id', 'budget_program_detail_id'], 'bpsd_schedule_detail_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_program_schedule_details');
    }
};
