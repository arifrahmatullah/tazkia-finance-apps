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
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'is_global']);
            $table->string('slug')->unique()->after('name');
            $table->string('icon')->nullable()->after('description');
            $table->string('color')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'icon', 'color']);
            $table->string('display_name')->after('name');
            $table->boolean('is_global')->default(false);
        });
    }
};
