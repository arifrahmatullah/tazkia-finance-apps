<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('requester_position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignUuid('approver_position_id')->constrained('positions')->cascadeOnDelete();
            $table->unsignedTinyInteger('step');
            $table->decimal('max_amount', 15, 2)->nullable()->comment('null = tanpa batas');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'requester_position_id', 'step'], 'appsetting_org_pos_step_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_settings');
    }
};
