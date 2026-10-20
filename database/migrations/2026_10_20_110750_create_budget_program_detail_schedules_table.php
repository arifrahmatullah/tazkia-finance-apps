<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Termin spesifik yang dipakai satu rincian -- buat rincian yang frekuensinya beda dari
    // program (mis. biaya sekali bayar di program yang jalan 2 termin). Kosong (tidak ada baris
    // sama sekali untuk satu detail_id) = default, rincian itu dianggap berlaku di SEMUA termin
    // (termasuk otomatis ikut termin baru kalau frekuensi program nanti ditambah) -- sama seperti
    // perilaku sebelum fitur ini ada. Begitu ada baris eksplisit, rincian dianggap "custom",
    // cuma berlaku di termin yang disimpan di sini. Pivot murni (dipakai lewat belongsToMany()
    // ->sync()), composite primary key tanpa kolom id tersendiri -- sync() cuma insert kolom
    // FK-nya lewat query builder, tidak lewat Eloquent, jadi kolom id UUID tanpa default akan
    // gagal diisi (pola yang sama dipakai role_permission & position_department_views).
    public function up(): void
    {
        Schema::create('budget_program_detail_schedules', function (Blueprint $table) {
            $table->foreignUuid('budget_program_detail_id')
                ->constrained('budget_program_details', 'id', 'bpds_detail_fk')
                ->cascadeOnDelete();
            $table->foreignUuid('budget_program_schedule_id')
                ->constrained('budget_program_schedules', 'id', 'bpds_schedule_fk')
                ->cascadeOnDelete();
            $table->primary(['budget_program_detail_id', 'budget_program_schedule_id'], 'bpds_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_program_detail_schedules');
    }
};
