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
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('fund_request_blocked')->default(false)->after('is_active');
            $table->string('fund_request_block_reason')->nullable()->after('fund_request_blocked');
            $table->timestamp('fund_request_blocked_at')->nullable()->after('fund_request_block_reason');
            $table->foreignUuid('fund_request_blocked_by')->nullable()->after('fund_request_blocked_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fund_request_blocked_by');
            $table->dropColumn(['fund_request_blocked', 'fund_request_block_reason', 'fund_request_blocked_at']);
        });
    }
};
