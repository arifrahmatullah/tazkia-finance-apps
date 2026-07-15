<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            // Jejak asal jurnal otomatis (pencairan/laporan/pengembalian dana)
            $table->string('source_type', 50)->nullable()->after('status');
            $table->uuid('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
