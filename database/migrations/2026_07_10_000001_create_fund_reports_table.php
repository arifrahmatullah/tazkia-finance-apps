<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fund_request_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('reported_by')->constrained('users')->restrictOnDelete();
            $table->date('report_date');
            $table->text('description');
            $table->decimal('amount_used', 15, 2);
            $table->enum('status', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_reports');
    }
};
