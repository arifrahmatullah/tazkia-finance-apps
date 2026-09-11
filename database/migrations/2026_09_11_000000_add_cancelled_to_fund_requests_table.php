<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE fund_requests MODIFY status ENUM('draft', 'pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'draft'");

        Schema::table('fund_requests', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            $table->string('cancelled_by')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancelled_by']);
        });

        DB::statement("ALTER TABLE fund_requests MODIFY status ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
    }
};
