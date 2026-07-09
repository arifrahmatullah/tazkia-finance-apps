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
            $table->foreignUuid('budget_program_id')->nullable()->constrained('budget_programs')->nullOnDelete()->after('budget_period_id');
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropForeign(['budget_program_id']);
            $table->dropColumn('budget_program_id');
        });
    }
};
