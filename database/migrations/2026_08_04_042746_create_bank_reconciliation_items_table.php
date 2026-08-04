<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bank_reconciliation_id')->constrained()->cascadeOnDelete();
            // buku  = transaksi ada di rekening koran tapi belum tercatat di aplikasi (perlu jurnal penyesuaian)
            // bank  = transaksi sudah tercatat di aplikasi tapi belum settle/muncul di rekening koran (beda waktu saja)
            $table->enum('side', ['buku', 'bank']);
            $table->string('description', 255);
            $table->decimal('amount', 15, 2);
            $table->foreignUuid('counter_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->foreignUuid('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
    }
};
