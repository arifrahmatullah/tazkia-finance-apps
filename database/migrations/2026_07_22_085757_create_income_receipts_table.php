<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('income_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('income_estimate_id')->constrained()->cascadeOnDelete();
            $table->date('receipt_date');
            $table->string('description');
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->string('proof_path')->nullable();
            $table->string('proof_name')->nullable();
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_receipts');
    }
};
