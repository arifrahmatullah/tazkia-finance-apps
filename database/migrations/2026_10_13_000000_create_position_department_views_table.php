<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Akses lihat tambahan lintas departemen untuk jabatan atasan (mis. Wakil Rektor
    // bisa lihat Program Kerja departemen-departemen di bawah bidangnya), tanpa harus
    // jabatan itu sendiri terdaftar di departemen tersebut. Pivot murni (dipakai lewat
    // belongsToMany()->sync()), composite primary key tanpa kolom id tersendiri --
    // sync() cuma insert kolom FK-nya lewat query builder, tidak lewat Eloquent, jadi
    // kolom id UUID tanpa default akan gagal diisi (pola yang sama dipakai role_permission).
    public function up(): void
    {
        Schema::create('position_department_views', function (Blueprint $table) {
            $table->foreignUuid('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->primary(['position_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_department_views');
    }
};
