<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_request_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fund_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('step');
            $table->foreignUuid('approver_position_id')->constrained('positions')->restrictOnDelete();
            $table->foreignUuid('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['fund_request_id', 'step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_request_approvals');
    }
};
