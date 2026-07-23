<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            // Idempotency key untuk API POST jurnal (source_type = 'api') — dikirim aplikasi lain
            $table->string('external_reference', 100)->nullable()->after('source_id');
            $table->unique(['organization_id', 'external_reference'], 'journal_entries_org_external_ref_unique');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique('journal_entries_org_external_ref_unique');
            $table->dropColumn('external_reference');
        });
    }
};
