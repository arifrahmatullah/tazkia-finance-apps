<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fund_request_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('fund_report_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);

            // pending  : tagihan dibuat, menunggu pengaju transfer & upload bukti
            // waiting  : bukti dikirim, menunggu konfirmasi keuangan
            // confirmed: keuangan sudah menerima dana, selesai
            $table->enum('status', ['pending', 'waiting', 'confirmed'])->default('pending');

            // Diisi pengaju saat mengembalikan dana
            $table->foreignUuid('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->foreignUuid('refund_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->text('payment_notes')->nullable();
            $table->string('proof_path', 500)->nullable();
            $table->string('proof_name', 255)->nullable();

            // Diisi keuangan saat konfirmasi / menolak bukti
            $table->foreignUuid('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('confirmation_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_refunds');
    }
};
