<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->foreignUuid('budget_program_schedule_id')->nullable()->after('budget_program_id')
                  ->constrained('budget_program_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropForeign(['budget_program_schedule_id']);
            $table->dropColumn('budget_program_schedule_id');
        });
    }
};
