<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// JournalEntry::generateReference() menomori referensi PER organisasi, tapi kolomnya
// sejak awal dibuat unique secara GLOBAL -- jadi dua organisasi berbeda yang sama-sama
// membuat jurnal pertamanya di bulan yang sama akan bentrok di JU-{YYYYMM}-0001. Ini
// baru ketahuan sekarang karena fitur Pengajuan Saldo memposting DUA jurnal (beda
// organisasi) sekaligus untuk satu transaksi, jadi tabrakan ini jadi sangat mudah terjadi.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique('journal_entries_reference_unique');
            $table->unique(['organization_id', 'reference'], 'journal_entries_org_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique('journal_entries_org_reference_unique');
            $table->unique('reference');
        });
    }
};
