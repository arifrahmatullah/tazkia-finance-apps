<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            // Snapshot nominal yang disetujui SEBELUM dikoreksi Keuangan saat pencairan (mis.
            // lampiran ternyata lebih kecil dari pengajuan). Null berarti tidak pernah dikoreksi
            // -- amount tetap sama dengan yang disetujui.
            $table->decimal('original_amount', 15, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropColumn('original_amount');
        });
    }
};
