<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_program_details', function (Blueprint $table) {
            $table->foreignUuid('account_id')->nullable()->after('budget_program_id')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budget_program_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
            $table->dropColumn('account_id');
        });
    }
};
