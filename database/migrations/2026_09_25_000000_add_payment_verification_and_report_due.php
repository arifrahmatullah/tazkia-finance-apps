<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_programs', function (Blueprint $table) {
            // Program berjenis "pembayaran" yang sudah dicek Keuangan dan dinyatakan memang
            // pembayaran (tidak perlu laporan) -- keluar dari antrean Verifikasi Pembayaran.
            $table->timestamp('payment_verified_at')->nullable();
            $table->string('payment_verified_by', 100)->nullable();
        });

        Schema::table('fund_requests', function (Blueprint $table) {
            // Batas lapor eksplisit. Null = default disbursed_at + 14 hari. Diisi saat jenis
            // program diubah Keuangan setelah dana cair (14 hari dihitung dari perubahan itu).
            $table->timestamp('report_due_at')->nullable()->after('disbursed_at');
        });
    }

    public function down(): void
    {
        Schema::table('budget_programs', function (Blueprint $table) {
            $table->dropColumn(['payment_verified_at', 'payment_verified_by']);
        });

        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropColumn('report_due_at');
        });
    }
};
