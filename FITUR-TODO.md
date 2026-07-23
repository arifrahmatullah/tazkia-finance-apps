# Checklist Fitur — Gap dari aplikasi-akunting & aplikasi-keuangan

> Hasil perbandingan fitur `tazkia-finance-apps` dengan `aplikasi-akunting` (Java Spring)
> dan `aplikasi-keuangan` (Java Spring) per 16 Juli 2026.
> Centang `[x]` jika fitur sudah selesai dikerjakan.

## ✅ Sudah ada di aplikasi ini

- Master data: organisasi, departemen, jabatan, karyawan, user, role & permission
- Chart of Accounts (COA / akun)
- Periode anggaran, alokasi anggaran, program kerja + detail + jadwal
- Estimasi pendapatan
- Setting approval + inbox approval
- Pengajuan dana → pencairan → laporan dana → pengembalian dana (termasuk bulk)
- Konfirmasi penerimaan dana oleh pengaju
- Jurnal umum + posting
- Template jurnal + API `GET /api/journal-templates`
- Audit log
- Dashboard staf

---

## 📌 Prioritas utama

- [x] **Laporan realisasi anggaran vs pagu** — perbandingan anggaran vs realisasi per mata anggaran/program kerja (ref: `BudgetRealizedController`, aplikasi-keuangan) ✅ `/reports/realisasi-anggaran`
- [x] **Laporan pencairan & pengajuan dana** — rekap untuk keuangan/pimpinan, filter periode/organisasi (ref: `DisbursementReportController`, `RequestFundReportController`) ✅ `/reports/pengajuan-dana` & `/reports/pencairan-dana`
- [x] **Buku besar per akun** — riwayat mutasi debit/kredit per akun (ref: `ReportAccountController`, aplikasi-akunting) ✅ `/reports/buku-besar`
- [x] **Neraca saldo (trial balance)** — saldo semua akun per periode (ref: `ReportController /report/neraca-saldo`) ✅ `/reports/neraca-saldo`
- [x] **Saldo awal (beginning balance)** — input saldo awal per akun per periode; prasyarat neraca balance (ref: `BeginningBalanceController`) ✅ `/beginning-balances`
- [x] **Penerimaan real (realisasi pendapatan)** — pencatatan penerimaan yang benar-benar masuk + rencana penerimaan per bulan (ref: `PenerimaanRealController`, `RencanaPenerimaanController`) ✅ `/income-estimates/{id}` (rencana per bulan = jadwal estimasi yang sudah ada, realisasi = fitur baru `income-receipts`)
- [x] **API POST jurnal** — endpoint agar aplikasi lain bisa mengirim jurnal otomatis, pelengkap API GET template (ref: `ApiJournalController /public/api/journal` & `/journal-array`) ✅ `POST /api/journal-entries` (lihat `docs/api-jurnal-template-dan-jurnal.md`)

## 📒 Akuntansi (ref: aplikasi-akunting)

- [ ] **Neraca / balance sheet** — laporan posisi keuangan (ref: `BalanceSheetController`)
- [ ] **Laporan keuangan configurable** — struktur laporan (laba rugi / laporan aktivitas) bisa disetting sendiri (ref: `SettingReportController`)
- [ ] **Jurnal penyesuaian & worksheet** — jurnal penyesuaian + kertas kerja (ref: `JournalController /journal/penyesuaian`, `/journal/woorksheet`)
- [ ] **Review jurnal** — alur review + update status jurnal (ref: `JournalController /journal/review`)
- [ ] **Export Excel** — download jurnal, laporan, dan worksheet ke Excel (ref: `ExcelJournalController /download/*`)

## 💰 Keuangan (ref: aplikasi-keuangan)

- [ ] **Kas kecil (petty cash)** — pencatatan transaksi kas kecil terpisah dari pengajuan dana (ref: `KasKecilController`)
- [ ] **Saldo bank & kas harian** — saldo per rekening bank + histori saldo harian + laporan kas harian (ref: `SaldoBankController`, `KasHarianReportController`)
- [ ] **Laporan detail transaksi** — rincian seluruh transaksi per periode (ref: `LaporanDetailTransaksiController`)
- [ ] **Laporan realisasi bulanan** — rekap realisasi per bulan (ref: `MonthlyRealizedController`)
- [ ] **Laporan estimasi pengeluaran** — proyeksi pengeluaran (ref: `EstimasiPengeluaranController`)
- [ ] **Laporan estimasi vs realisasi pendapatan** (ref: `EstimateRevenueReportController`, `RealizedRevenuesReportController`)
- [ ] **Standar biaya + satuan** — master standar harga satuan sebagai acuan/validasi nilai pengajuan (ref: `StandardController`, `StandardDetailController`, `SatuanDao`)
- [ ] **PIC & porsi anggaran** — penanggung jawab per mata anggaran + pembagian porsi antar unit (ref: `PicAnggaranController`, `AnggaranDetailPorsiController`)
- [ ] **Blokir pengajuan** — tutup/blokir pengajuan dana pada periode tertentu (ref: `BlockPengajuanDao`)
- [ ] **Kwitansi / terbilang** — cetak bukti pencairan dengan nominal terbilang (ref: `Terbilang.java`)

## 🤔 Kemungkinan tidak relevan (spesifik aplikasi lama)

- [ ] Piutang mahasiswa + jenis tagihan — integrasi billing kampus (ref: `StudentReceivablesController`, `BillTypeController`)
- [ ] Klasifikasi / subklasifikasi akun (ref: `ClassificationController`)
- [ ] Master kategori template jurnal — di sini sudah pakai kategori free-text (ref: `CategoryJournalTemplateController`)
