<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_topup_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requesting_organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('target_account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignUuid('source_credit_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignUuid('requested_by')->constrained('employees')->restrictOnDelete();
            // Referensi unik PER organisasi pengaju (bukan global) -- generateReference()
            // menomori ulang tiap organisasi, jadi dua organisasi anak bisa punya nomor urut
            // sama di bulan yang sama (mis. SLD-202609-0001 buat Kampus DAN buat STMIK).
            $table->string('reference', 30);
            $table->decimal('amount', 18, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->foreignUuid('yayasan_source_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignUuid('yayasan_debit_account_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->string('proof_path')->nullable();
            $table->string('proof_name')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['requesting_organization_id', 'reference'], 'ctr_org_reference_unique');
        });

        // Pivot informational -- daftar pengajuan dana yang jadi justifikasi/konteks
        // permintaan saldo ini (bukan penjatahan mengikat, cuma konteks buat Yayasan).
        Schema::create('cash_topup_request_fund_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cash_topup_request_id');
            $table->uuid('fund_request_id');
            $table->timestamps();

            $table->foreign('cash_topup_request_id', 'ctrfr_topup_id_foreign')
                ->references('id')->on('cash_topup_requests')->cascadeOnDelete();
            $table->foreign('fund_request_id', 'ctrfr_fund_request_id_foreign')
                ->references('id')->on('fund_requests')->cascadeOnDelete();
            $table->unique(['cash_topup_request_id', 'fund_request_id'], 'ctrfr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_topup_request_fund_requests');
        Schema::dropIfExists('cash_topup_requests');
    }
};
