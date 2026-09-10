<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_program_schedules', function (Blueprint $table) {
            $table->decimal('amount', 18, 2)->nullable()->after('estimated_date');
        });
    }

    public function down(): void
    {
        Schema::table('budget_program_schedules', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
