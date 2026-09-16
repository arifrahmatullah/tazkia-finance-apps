<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_topup_requests', function (Blueprint $table) {
            // 'requested'         : Kampus/STMIK mengajukan, Yayasan menyetujui (alur lama).
            // 'yayasan_initiated' : Yayasan langsung mengisi saldo tanpa menunggu permintaan --
            //                       status langsung 'approved' saat dibuat, tidak lewat 'pending'.
            $table->enum('origin', ['requested', 'yayasan_initiated'])
                ->default('requested')->after('requesting_organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('cash_topup_requests', function (Blueprint $table) {
            $table->dropColumn('origin');
        });
    }
};
