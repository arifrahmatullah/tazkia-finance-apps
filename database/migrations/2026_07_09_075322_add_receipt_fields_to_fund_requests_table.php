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
            $table->enum('receipt_status', ['confirmed', 'disputed'])->nullable()->after('disburse_account_id');
            $table->timestamp('receipt_confirmed_at')->nullable()->after('receipt_status');
            $table->text('receipt_notes')->nullable()->after('receipt_confirmed_at');
            $table->boolean('auto_confirmed')->default(false)->after('receipt_notes');
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropColumn(['receipt_status', 'receipt_confirmed_at', 'receipt_notes', 'auto_confirmed']);
        });
    }
};
