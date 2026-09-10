<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_program_change_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('change_request_id')->constrained('budget_program_change_requests')->cascadeOnDelete();
            $table->unsignedTinyInteger('step');
            $table->string('approver_role', 30); // keuangan | step2 (posisi khusus, lihat budget_program_approval_positions)
            $table->foreignUuid('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('waiting'); // waiting | approved | rejected
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['change_request_id', 'step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_program_change_approvals');
    }
};
