<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->timestamp('disbursed_at')->nullable()->after('rejected_at');
            $table->string('disbursement_notes', 500)->nullable()->after('disbursed_at');
            $table->string('disbursed_by', 150)->nullable()->after('disbursement_notes');
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropColumn(['disbursed_at', 'disbursement_notes', 'disbursed_by']);
        });
    }
};
