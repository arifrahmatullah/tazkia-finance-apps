<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cash_topup_request_fund_requests salah dikasih primary key 'id' UUID terpisah --
// beda dari konvensi pivot table lain di aplikasi ini (lihat role_permission: cukup
// composite primary key tanpa kolom id). Karena belongsToMany() standar tidak pernah
// mengisi kolom id UUID itu, SETIAP attach() ke tabel ini gagal dengan error "Field
// 'id' doesn't have a default value" -- tabel ini pasti kosong (0 baris) di semua
// environment, jadi aman diubah strukturnya tanpa kehilangan data.
return new class extends Migration
{
    public function up(): void
    {
        // Drop FKs dulu -- ctrfr_topup_id_foreign bergantung pada index ctrfr_unique
        // (kolom cash_topup_request_id ada di posisi pertama index itu), jadi index-nya
        // tidak bisa dihapus selama FK itu masih ada.
        Schema::table('cash_topup_request_fund_requests', function (Blueprint $table) {
            $table->dropForeign('ctrfr_topup_id_foreign');
            $table->dropForeign('ctrfr_fund_request_id_foreign');
            $table->dropUnique('ctrfr_unique');
            $table->dropPrimary();
            $table->dropColumn('id');
        });

        Schema::table('cash_topup_request_fund_requests', function (Blueprint $table) {
            $table->primary(['cash_topup_request_id', 'fund_request_id']);
            $table->foreign('cash_topup_request_id', 'ctrfr_topup_id_foreign')
                ->references('id')->on('cash_topup_requests')->cascadeOnDelete();
            $table->foreign('fund_request_id', 'ctrfr_fund_request_id_foreign')
                ->references('id')->on('fund_requests')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_topup_request_fund_requests', function (Blueprint $table) {
            $table->dropForeign('ctrfr_topup_id_foreign');
            $table->dropForeign('ctrfr_fund_request_id_foreign');
            $table->dropPrimary();
        });

        Schema::table('cash_topup_request_fund_requests', function (Blueprint $table) {
            $table->uuid('id')->first();
            $table->primary('id');
            $table->unique(['cash_topup_request_id', 'fund_request_id'], 'ctrfr_unique');
            $table->foreign('cash_topup_request_id', 'ctrfr_topup_id_foreign')
                ->references('id')->on('cash_topup_requests')->cascadeOnDelete();
            $table->foreign('fund_request_id', 'ctrfr_fund_request_id_foreign')
                ->references('id')->on('fund_requests')->cascadeOnDelete();
        });
    }
};
