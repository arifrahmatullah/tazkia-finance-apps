<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_program_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('budget_program_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('action', 30); // update_info | add_detail | update_detail | delete_detail | update_schedule
            $table->uuid('subject_id')->nullable(); // budget_program_detail_id / budget_program_schedule_id, tergantung action
            $table->json('payload')->nullable();
            $table->string('summary', 255)->nullable();
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->unsignedTinyInteger('total_steps')->default(2);
            $table->timestamps();

            $table->index(['budget_program_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_program_change_requests');
    }
};
