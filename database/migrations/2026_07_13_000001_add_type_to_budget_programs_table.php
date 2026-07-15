<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_programs', function (Blueprint $table) {
            $table->enum('type', ['pengadaan', 'kegiatan', 'pembayaran'])->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('budget_programs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
