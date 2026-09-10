<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Konfigurasi jabatan approver step-2 (setelah Keuangan) untuk edit Program Kerja
    // di luar periode perencanaan -- satu baris per organisasi (mis. Warek Bidang Sumberdaya).
    public function up(): void
    {
        Schema::create('budget_program_approval_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('step2_position_id')->constrained('positions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_program_approval_positions');
    }
};
