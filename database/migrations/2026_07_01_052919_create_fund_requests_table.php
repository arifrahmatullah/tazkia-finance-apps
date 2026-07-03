<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('budget_period_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('requester_id')->constrained('employees')->restrictOnDelete();
            $table->foreignUuid('requester_position_id')->constrained('positions')->restrictOnDelete();
            $table->string('reference', 30)->unique();
            $table->string('title', 200);
            $table->text('purpose')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->unsignedTinyInteger('current_step')->default(0);
            $table->unsignedTinyInteger('total_steps')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_requests');
    }
};
