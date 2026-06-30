# Aplikasi Keuangan & Akunting Terpadu — Tazkia

> Status: **In Progress**  
> Tanggal: 30 Juni 2026  
> Stack: Laravel + MySQL

---

## Latar Belakang & Masalah

Sebelumnya ada dua aplikasi terpisah:
- `aplikasi-keuangan` — pencatatan transaksi keuangan
- `aplikasi-akunting` — pembukuan & laporan

**Masalah utama:** Data harus ditarik manual dari aplikasi keuangan ke akunting → rawan error, duplikasi kerja, lambat.

**Solusi:** Satu aplikasi terintegrasi. Setiap transaksi keuangan otomatis menghasilkan jurnal akuntansi — tanpa input manual dua kali.

---

## Target Pengguna

| Pengguna | Entitas |
|---|---|
| Staf / Pemohon Anggaran | Kampus |
| Kepala Unit / Departemen | Kampus |
| Keuangan Kampus | Kampus |
| Keuangan Yayasan | Yayasan |
| Akunting | Kampus / Yayasan |
| Pimpinan (view only) | Kampus / Yayasan |
| Admin Sistem | — |

**Fokus awal:** Kampus dulu, lalu dikembangkan ke Yayasan & SaaS multi-tenant.

---

## Temuan dari Aplikasi Lama

### aplikasi-keuangan (529 commits, sejak 2019)

**Stack:** Java Spring Boot + Thymeleaf + MySQL + Flyway

#### Tabel Database

| Tabel | Fungsi |
|---|---|
| `departemen` | Unit/departemen kampus (kode, nama) |
| `karyawan` | Data pegawai (NIK, NIDN, email, jabatan) |
| `jabatan` | Jabatan pegawai |
| `instansi` | Data instansi |
| `mata_anggaran` | Item anggaran (kode + nama) |
| `periode_anggaran` | Periode anggaran (tanggal mulai-selesai perencanaan & realisasi) |
| `pagu_anggaran` | Plafon anggaran per departemen per periode |
| `pic_anggaran` | PIC anggaran per departemen |
| `anggaran` | Anggaran per departemen per periode (kode, nama, amount) |
| `anggaran_detail` | Rincian anggaran (deskripsi, kuantitas, amount per item) |
| `rencana_penerimaan` | Rencana penerimaan (pagu, anggaran, selisih) |
| `penerimaan` | Realisasi penerimaan |
| `bank` + `rekening` | Data bank dan rekening |
| `s_user`, `s_role`, `s_permission` | Manajemen user & hak akses |

#### Entity Transaksi (17 entity)

| Entity | Fungsi |
|---|---|
| `PengajuanDana` | Pengajuan dana — link ke anggaran, anggaran_detail, karyawan, departemen, periode |
| `PengajuanDanaApprove` | Approval bertingkat — `nomorUrut` = level approval, bisa revisi nominal |
| `PencairanDana` | Pencairan dana — link ke pengajuan, punya `statusAkunting` & `journalTemplate` |
| `PencairanDanaDetail` | Detail pencairan |
| `LaporanDana` (SPJ) | Pertanggungjawaban — upload file, punya status `posting` (WAITING/POSTED) |
| `LaporanDanaFile` | File lampiran SPJ |
| `LaporanDanaPorsi` | Porsi laporan dana |
| `KembalianDana` | Pengembalian sisa dana |
| `PengembalianDana` | Proses pengembalian |
| `KasKecil` | Kas kecil |
| `PenerimaanReal` | Realisasi penerimaan |
| `SaldoBank` | Saldo bank harian |

#### Titik Integrasi Lama (Sumber Masalah)

Di entity `PencairanDana` ada field:
```java
private StatusAccounting statusAkunting;  // default: WAITING
private String journalTemplate;           // referensi template jurnal
```

Di entity `LaporanDana` ada field:
```java
private StatusRecord posting;  // default: WAITING → harus manual di-POSTED ke akunting
```

**Inilah akar masalahnya:** Ketika pencairan cair, `statusAkunting = WAITING`. Seseorang harus manual membuka aplikasi akunting, narik data ini, lalu buat jurnal. Di sistem baru, ini harus **otomatis**.

#### Alur Approval yang Sudah Ada

```
PengajuanDana
  └── PengajuanDanaApprove (nomorUrut=1) → jabatanApprove, statusApprove, bisa revisi nominal
  └── PengajuanDanaApprove (nomorUrut=2) → level berikutnya
  └── ... (multi-level)
      ↓
PencairanDana (statusCair=WAITING → CAIR, statusAkunting=WAITING → belum ke akunting)
      ↓
LaporanDana / SPJ (posting=WAITING → POSTED)
```

---

### aplikasi-akunting (144 commits, sejak 2024)

**Stack:** Java Spring Boot + Thymeleaf + PostgreSQL + Flyway

#### Struktur Database yang Sudah Ada

```
yayasan          → entitas yayasan
  └── institut   → entitas kampus/institut (FK ke yayasan)

classification        → klasifikasi utama (Aset, Liabilitas, Pendapatan, Beban)
  └── subclassification → sub-klasifikasi (FK ke classification)
        └── account    → akun (FK ke subclassification)

account_institut  → mapping many-to-many: akun ↔ institut
```

### Chart of Accounts (CoA) Lama — 62 Akun

| Kode | Kelompok |
|---|---|
| **1.x** | Aset |
| **2.x** | Liabilitas |
| **4.1.x** | Pendapatan Mahasiswa (uang pendaftaran, asrama, dll) |
| **4.2.x** | Pendapatan Non-Mahasiswa (sewa gedung, kerjasama, dll) |
| **5.1.x** | Biaya Operasional Pendidikan (dosen, tenaga kependidikan, dll) |

### Field Tabel Account (Existing)

| Field | Tipe | Keterangan |
|---|---|---|
| `id` | UUID | Primary key |
| `code` | VARCHAR | Kode akun (misal: 1.1.1) |
| `name` | VARCHAR | Nama akun |
| `amount` | DECIMAL(25) | Saldo |
| `account_type` | ENUM | Tipe akun |
| `nominal_balance` | ENUM | Normal balance (DEBET/CREDIT) |
| `id_subclassification` | FK | Link ke subklasifikasi |
| `status` | ENUM | ACTIVE / INACTIVE |

### Fitur Tambah Akun (Form Lama)

Form di `/account/save` memiliki field:
- **Code** — kode akun manual
- **Name** — nama akun
- **Subclassification** — dropdown (tampil: "sub-name (classification-name)")
- **Account Type** — dropdown enum
- **Nominal Balance** — dropdown (DEBET / CREDIT)
- **Type Institution** — multi-select (akun bisa dipasang ke banyak institut)

### Cara Lama Mengelola Akun per Institut

Pendekatan lama: **satu CoA bersama, di-assign ke institut via `account_institut`**

→ Artinya Yayasan dan Kampus pakai tabel akun yang sama, tapi setiap akun bisa dipilih untuk dipakai di Yayasan saja, Kampus saja, atau keduanya.

**Masalah pendekatan ini:**
- Kode akun tidak bisa sama antar institut (misal Yayasan & Kampus tidak bisa sama-sama punya akun `1.1.1`)
- Laporan per institusi bergantung pada filter, bukan buku besar terpisah

---

---

## Perbandingan Dua Aplikasi Lama

| Aspek | aplikasi-keuangan | aplikasi-akunting |
|---|---|---|
| **Database** | MySQL | PostgreSQL |
| **Frontend** | Thymeleaf | Thymeleaf |
| **Fokus** | Anggaran, pengajuan, pencairan | Jurnal, buku besar, laporan |
| **Integrasi** | Push manual via `statusAkunting` | Terima data dari keuangan |
| **Multi-entitas** | Tidak ada yayasan/kampus | Ada `yayasan` + `institut` |
| **Approval** | Multi-level (`nomorUrut`) | Tidak ada |
| **SPJ/Laporan** | Ada (`LaporanDana`) | Tidak ada |

### Yang Perlu Dipertahankan di Sistem Baru

Dari **aplikasi-keuangan:**
- ✅ Struktur pengajuan dana + multi-level approval (nomorUrut)
- ✅ Revisi nominal saat approval
- ✅ SPJ dengan upload file
- ✅ KembalianDana (pengembalian sisa)
- ✅ Pagu anggaran per departemen per periode
- ✅ Rencana penerimaan vs realisasi

Dari **aplikasi-akunting:**
- ✅ Struktur CoA: Classification → Subclassification → Account
- ✅ Journal Template (buat jurnal otomatis dari template)
- ✅ Report Setting (konfigurasi tampilan laporan keuangan)
- ✅ Multi-entitas (Yayasan + Institut)
- ✅ Saldo awal (Beginning Balance)
- ✅ Daily Balance tracking

### Yang Perlu Diperbaiki / Ditambah

- ❌ **Integrasi manual** → ganti dengan **jurnal otomatis** saat pencairan diproses
- ❌ **Database terpisah** (MySQL vs PostgreSQL) → **satu PostgreSQL**
- ❌ **Tidak ada dashboard terpadu** → tambah dashboard dengan KPI keuangan
- ❌ **Tidak ada multi-entitas di keuangan** → tambah konteks yayasan/kampus
- ❌ **Tidak ada rekonsiliasi bank** → tambah di fase 2

---

## Poin Diskusi yang Perlu Disepakati

### 1. Struktur Entitas — Yayasan vs Kampus

Dari aplikasi lama, pendekatan yang dipakai adalah **Opsi B (CoA bersama dengan assignment ke institut)**. Untuk sistem baru ada dua pilihan:

✅ **KEPUTUSAN: CoA Terpisah per Entitas**
- Yayasan punya CoA & buku besar sendiri
- Kampus punya CoA & buku besar sendiri
- Kode akun bisa sama antar entitas (karena terpisah, tidak konflik)
- Laporan konsolidasi dibuat dengan menjumlah saldo dari dua entitas
- Cocok untuk multi-tenant SaaS nanti

**Implikasi ke database:** Setiap tabel yang berhubungan dengan akun & jurnal harus punya kolom `id_entitas` (atau FK ke tabel `entitas`) sebagai partisi data.

---

### 2. Role & Hak Akses

> **Pertanyaan:** Apakah role di bawah sudah sesuai? Ada yang perlu ditambah?

| Role | Akses Utama |
|---|---|
| **Staf / Pemohon** | Buat & pantau pengajuan anggaran |
| **Kepala Unit** | Approve pengajuan dari staf di unitnya |
| **Keuangan Kampus** | Verifikasi & proses pencairan, kelola kas kampus |
| **Keuangan Yayasan** | ACC & cairkan anggaran, kelola kas yayasan |
| **Akunting** | Jurnal, buku besar, laporan keuangan |
| **Pimpinan** | Dashboard & laporan (read-only) |
| **Admin** | Kelola user, master data, konfigurasi sistem |

---

### 3. Alur Pengajuan Anggaran

> **Pertanyaan:** Apakah alur ini sesuai dengan proses di Tazkia? Berapa level approval?

```
[Staf] Buat Pengajuan Anggaran
          ↓
[Kepala Unit] Review & Approve
          ↓
[Keuangan Kampus] Verifikasi & Proses
          ↓
[Keuangan Yayasan] Final Approval & Cairkan
          ↓
[Sistem] Generate Jurnal Otomatis
          ↓
[Staf] Konfirmasi Penerimaan Dana
          ↓
[Staf] Upload Bukti Pertanggungjawaban (SPJ)
```

Hal yang perlu dikonfirmasi:
- [ ] Berapa level approval yang ada? (contoh: Kaprodi → Dekan → Keuangan?)
- [ ] Apakah ada batas nominal yang menentukan perlu tidaknya approval Yayasan?
- [ ] Apakah pengajuan bisa direvisi setelah ditolak, atau harus buat baru?
- [ ] Apakah SPJ (Surat Pertanggungjawaban) perlu diupload sebagai lampiran?

---

### 4. Integrasi Jurnal Otomatis (Inti Sistem)

> Ini yang membedakan sistem baru vs sistem lama.

Contoh alur jurnal otomatis:

**Skenario:** Staf dapat pencairan Rp 5.000.000 untuk Seminar Nasional

```
Kategori Anggaran: Beban Kegiatan Akademik
Akun CoA terpetakan: 6.1.01 - Beban Seminar

Jurnal yang ter-generate otomatis:
  Debit  : 6.1.01 Beban Seminar        Rp 5.000.000
  Kredit : 1.1.02 Kas / Bank Kampus    Rp 5.000.000
```

Mapping dilakukan lewat **kategori anggaran → akun CoA** yang dikonfigurasi admin.

Hal yang perlu dikonfirmasi:
- [ ] Apakah setiap kategori anggaran sudah terpetakan ke akun CoA tertentu?
- [ ] Siapa yang bisa mengubah mapping ini? (Admin / Akunting?)
- [ ] Bagaimana jika pencairan bertahap (uang muka dulu, sisanya belakangan)?

---

### 5. Tech Stack

Dari repo lama terlihat sudah pakai: **Java Spring Boot + Thymeleaf + Maven + PostgreSQL + Flyway**

> **Pertanyaan:** Lanjutkan Thymeleaf (seperti sebelumnya) atau ganti React/Next.js?

| | Thymeleaf (seperti lama) | React / Next.js |
|---|---|---|
| Kurva belajar | Rendah (sudah familiar) | Lebih tinggi |
| Pengalaman UX | Standar, page-reload | Lebih interaktif, SPA |
| Mobile (nanti) | Tidak bisa share | Bisa share API ke mobile |
| Cocok untuk SaaS | Cukup | Lebih ideal |
| Setup awal | Cepat | Lebih kompleks |

**Stack yang dikonfirmasi:**

| Layer | Teknologi |
|---|---|
| Framework | Laravel 11 |
| Frontend | Blade + Livewire / Alpine.js |
| Database | MySQL |
| Migrasi DB | Laravel Migration |
| ORM | Eloquent |
| Auth | Laravel Breeze / Fortify + Spatie Permission |
| Laporan PDF | DomPDF (barstorm/laravel-dompdf) |
| Export Excel | Maatwebsite Excel |
| Queue | Laravel Queue + Redis |
| Container | Docker + Docker Compose |

---

### 6. Periode & Tahun Buku

> **Pertanyaan:** Tahun buku ikut tahun kalender (Jan–Des) atau tahun ajaran (Jul–Jun)?

Ini berpengaruh ke:
- Tutup buku akhir periode
- Laporan tahunan
- Carry-forward saldo anggaran

---

## Modul yang Akan Dibangun

### Fase 1 — MVP (Fokus Kebutuhan Inti Kampus)

```
✅ Modul 1: Master Data
   - Chart of Accounts (CoA) sesuai PSAK
   - Manajemen user & role
   - Data unit/departemen
   - Kategori anggaran (mapping ke CoA)

✅ Modul 2: Pengajuan & Pencairan Anggaran
   - Form pengajuan oleh staf
   - Workflow approval multi-level
   - Notifikasi email/in-app
   - Pencairan & konfirmasi
   - Upload SPJ

✅ Modul 3: Kas & Bank
   - Pencatatan kas masuk/keluar
   - Multi-rekening (kas kampus, kas yayasan, bank)
   - Transfer antar rekening

✅ Modul 4: Jurnal & Buku Besar (Otomatis + Manual)
   - Auto-generate dari transaksi
   - Jurnal manual untuk koreksi
   - Buku besar per akun
   - Trial balance

✅ Modul 5: Laporan Keuangan Dasar
   - Laporan Laba Rugi
   - Neraca
   - Laporan Arus Kas
   - Export PDF & Excel
```

### Fase 2 — Pengembangan Lanjutan

```
⬜ Anggaran Tahunan (Budget vs Aktual)
⬜ Piutang & Hutang (AR/AP)
⬜ Rekonsiliasi Bank
⬜ Aset Tetap & Depresiasi
⬜ Dashboard & Grafik Analitik
⬜ Perpajakan (PPN, PPh)
```

### Fase 3 — SaaS & Multi-Tenant

```
⬜ Multi-company / Multi-tenant
⬜ Mobile App
⬜ API publik untuk integrasi
⬜ Billing & subscription
```

---

## Struktur Aplikasi (Rencana Awal)

```
tazkia/
├── backend/                    # Java Spring Boot
│   ├── src/main/java/
│   │   └── id/ac/tazkia/
│   │       ├── auth/           # Login, JWT, Role
│   │       ├── master/         # CoA, user, departemen
│   │       ├── anggaran/       # Pengajuan & approval
│   │       ├── keuangan/       # Kas, bank, transaksi
│   │       ├── akunting/       # Jurnal, buku besar
│   │       └── laporan/        # Report generator
│   └── src/main/resources/
│       └── application.yml
├── frontend/                   # TBD (Thymeleaf / React)
├── docker-compose.yml
└── PLANNING.md                 # File ini
```

---

## Keputusan yang Masih Perlu Didiskusikan

| # | Topik | Opsi |
|---|---|---|
| 1 | **Struktur CoA** | ✅ Terpisah per entitas |
| 2 | **Frontend** | Thymeleaf (seperti lama) / React+Next.js |
| 3 | **Role** | Apakah daftar role sudah lengkap? |
| 4 | **Alur approval** | Berapa level? Ada batas nominal? |
| 5 | **SPJ** | Perlu upload bukti pertanggungjawaban? |
| 6 | **Tahun buku** | Jan–Des atau Jul–Jun (tahun ajaran)? |
| 7 | **Pencairan bertahap** | Uang muka + pelunasan? |
| 8 | **Ekuitas / Modal** | Kode akun 3.x untuk ekuitas — perlu? |

---

## Struktur CoA Baru (Usulan)

Berdasarkan CoA lama + standar PSAK untuk lembaga pendidikan:

```
1. ASET
   1.1 Aset Lancar
       1.1.1 Kas dan Setara Kas
       1.1.2 Piutang Mahasiswa
       1.1.3 Piutang Lainnya
       1.1.4 Persediaan
   1.2 Aset Tidak Lancar
       1.2.1 Tanah
       1.2.2 Gedung
       1.2.3 Peralatan & Inventaris
       1.2.4 Kendaraan
       1.2.x Akumulasi Depresiasi (masing-masing)

2. LIABILITAS
   2.1 Liabilitas Jangka Pendek
       2.1.1 Hutang Gaji
       2.1.2 Hutang Lainnya
   2.2 Liabilitas Jangka Panjang
       2.2.1 Hutang Bank

3. EKUITAS / NET ASET
   3.1 Modal / Dana Yayasan
   3.2 Surplus / Defisit Tahun Berjalan
   3.3 Saldo Laba Ditahan

4. PENDAPATAN
   4.1 Pendapatan Mahasiswa
       4.1.1 Uang Pendaftaran
       4.1.2 SPP / UKT
       4.1.3 Uang Gedung
       4.1.4 Pendapatan Asrama
   4.2 Pendapatan Non-Mahasiswa
       4.2.1 Pendapatan Sewa
       4.2.2 Pendapatan Kerjasama
       4.2.3 Pendapatan Lainnya

5. BEBAN
   5.1 Beban Operasional Pendidikan
       5.1.1 Biaya Dosen
       5.1.2 Biaya Tenaga Kependidikan
       5.1.3 Biaya Operasional Pembelajaran
   5.2 Beban Umum & Administrasi
       5.2.1 Biaya Listrik & Air
       5.2.2 Biaya Pemeliharaan
       5.2.3 Biaya ATK
   5.3 Beban Kegiatan
       5.3.1 Biaya Seminar/Workshop
       5.3.2 Biaya Kegiatan Mahasiswa
```

> **Pertanyaan:** Apakah struktur ini cocok? Ada akun yang perlu ditambah atau disesuaikan?

---

*Dokumen ini akan diupdate seiring diskusi berlanjut.*
