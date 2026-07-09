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
            $table->foreignUuid('disburse_account_id')->nullable()->after('disbursed_by')
                  ->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropForeign(['disburse_account_id']);
            $table->dropColumn('disburse_account_id');
        });
    }
};
