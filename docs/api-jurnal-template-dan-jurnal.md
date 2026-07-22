# SRS — API Template Jurnal & API Kirim Jurnal dari Aplikasi Lain

| | |
|---|---|
| Dokumen | Software Requirements Specification (SRS) — API Integrasi Jurnal |
| Aplikasi | tazkia-finance-apps |
| Referensi | `aplikasi-akunting` — `ApiJournalTemplateController`, `ApiJournalController` |
| Status | Lihat tabel status implementasi di bawah |
| Terakhir diperbarui | 2026-07-22 |

## 1. Pendahuluan

### 1.1 Tujuan

Dokumen ini menspesifikasikan API yang dipakai **aplikasi lain** (mis. sistem SPMB, sistem akademik,
sistem pembayaran) untuk:

1. Membaca daftar **template jurnal** yang tersedia di `tazkia-finance-apps`, beserta baris debit/kreditnya.
2. Mengirim (POST) **transaksi jurnal** ke `tazkia-finance-apps` berdasarkan salah satu template tersebut,
   tanpa perlu tahu detail chart of account secara manual — cukup kirim kode template + nominal.

### 1.2 Ruang lingkup

Dokumen ini **tidak** membahas UI web (menu "Template Jurnal" / "Jurnal Umum" yang dipakai staf akunting
secara manual) — itu sudah berjalan dan tidak berubah. Fokus dokumen ini murni jalur **server-to-server**
lewat `routes/api.php`.

### 1.3 Status implementasi

| Endpoint | Method | Status | Keterangan |
|---|---|---|---|
| `/api/journal-templates` | GET | ✅ **Sudah tersedia** | Implementasi: `App\Http\Controllers\Api\JournalTemplateApiController@index` |
| `/api/journal-templates/{id}` | GET | ✅ **Sudah tersedia** | `JournalTemplateApiController@show` |
| `/api/journal-entries` | POST | 🚧 **Rancangan (belum dibangun)** | Ini spesifikasi untuk item TODO *"API POST jurnal"* — belum ada controller/route-nya di kode saat ini |

Bagian 3 mendokumentasikan API yang **sudah bisa dipakai hari ini**. Bagian 4 adalah **spesifikasi rancangan**
untuk endpoint POST yang belum dibangun, supaya tim aplikasi lain bisa mulai menyiapkan integrasinya dan tim
`tazkia-finance-apps` punya acuan saat mengimplementasikannya nanti.

## 2. Autentikasi & Konvensi Umum

### 2.1 Base URL

```
{APP_URL}/api
```

`APP_URL` mengikuti environment (mis. `http://localhost:8000` saat development, atau domain produksi).
Seluruh endpoint pada dokumen ini berada di bawah prefix `/api` (bukan `/beginning-balances`, dst — itu
route web yang butuh login sesi, bukan API key).

### 2.2 Autentikasi — API Key

Semua endpoint `/api/*` dilindungi middleware `api.key` (`App\Http\Middleware\VerifyApiKey`). Setiap
request **wajib** menyertakan header:

```
X-API-Key: <nilai EXTERNAL_API_KEY>
```

- Key dikonfigurasi lewat environment variable `EXTERNAL_API_KEY` di `.env` server `tazkia-finance-apps`.
- Saat ini **satu key tunggal** dipakai untuk semua partner (belum ada key per-aplikasi). Bila butuh
  membedakan/mencabut akses per partner di kemudian hari, ini perlu ditingkatkan (lihat §5).
- Key dibandingkan dengan `hash_equals()` (aman terhadap timing attack).

Response bila key tidak dikirim / salah:

```json
HTTP 401
{
  "response_code": "401",
  "response_message": "API key tidak valid."
}
```

Response bila server belum dikonfigurasi (`EXTERNAL_API_KEY` kosong):

```json
HTTP 503
{
  "response_code": "503",
  "response_message": "API belum dikonfigurasi. Set EXTERNAL_API_KEY di file .env."
}
```

### 2.3 Format response

Semua endpoint mengembalikan JSON dengan amplop yang sama:

```json
{
  "response_code": "200",
  "response_message": "Success",
  "data": { }
}
```

| Field | Tipe | Keterangan |
|---|---|---|
| `response_code` | string | Kode status ala HTTP, dalam bentuk string (`"200"`, `"400"`, `"401"`, dst) |
| `response_message` | string | Pesan singkat, kadang berbahasa Indonesia (mis. validasi) |
| `data` | object / array / null | Isi data. `null` atau tidak ada saat request gagal |

### 2.4 Kode response umum

| HTTP status | `response_code` | Kapan terjadi |
|---|---|---|
| 200 | `"200"` | Berhasil |
| 400 | `"400"` | Body tidak valid / jurnal tidak balance |
| 401 | `"401"` | API key hilang/salah |
| 403 | `"403"` | Template/akun bukan milik organisasi yang diminta |
| 404 | `"404"` | Template jurnal tidak ditemukan / tidak aktif |
| 422 | `"422"` | Data valid secara format tapi melanggar aturan bisnis (lihat §4.3) |
| 500 | `"ERR500"` | Kesalahan server tak terduga |
| 503 | `"503"` | `EXTERNAL_API_KEY` belum di-set di server |

## 3. API Template Jurnal (LIVE)

Dipakai aplikasi lain untuk menampilkan pilihan template + mengetahui akun apa saja yang akan
kena debit/kredit sebelum mengirim transaksi lewat §4.

### 3.1 `GET /api/journal-templates`

Daftar semua template jurnal beserta baris-barisnya.

**Query parameter (semua opsional):**

| Parameter | Tipe | Keterangan |
|---|---|---|
| `organization_id` | uuid | Filter berdasarkan ID organisasi |
| `organization_code` | string | Filter berdasarkan kode organisasi (alternatif `organization_id`, lebih praktis untuk aplikasi eksternal karena tidak perlu tahu UUID internal) |
| `category` | string | Filter kategori template (free-text, sesuai yang diisi saat template dibuat) |
| `search` | string | Cari di kolom `code` atau `name` (partial match) |
| `include_inactive` | boolean (`1`/`0`) | `1` untuk ikut menampilkan template non-aktif. Default: hanya yang aktif |

**Contoh request:**

```bash
curl -s "https://finance.tazkia.ac.id/api/journal-templates?organization_code=TZK&category=SPP" \
  -H "X-API-Key: ${EXTERNAL_API_KEY}"
```

**Contoh response — `200 OK`:**

```json
{
  "response_code": "200",
  "response_message": "Success",
  "data": [
    {
      "id": "0199f2c1-2b1a-7000-9c40-1a2b3c4d5e6f",
      "code": "JT-SPP-001",
      "name": "Penerimaan SPP Mahasiswa",
      "category": "SPP",
      "is_active": true,
      "organization": {
        "id": "019f26b5-4f3b-718c-9b61-f95d18297b58",
        "code": "TZK",
        "name": "Kampus Tazkia"
      },
      "details": [
        {
          "sequence": 0,
          "balance_type": "debit",
          "description": "Kas/Bank masuk",
          "account": {
            "id": "c4cbe5b7-8e5f-4878-89f7-83483842ea12",
            "code": "1-1002",
            "name": "Bank BSI 7888866641",
            "account_type": "aset",
            "normal_balance": "debit"
          }
        },
        {
          "sequence": 1,
          "balance_type": "credit",
          "description": "Pendapatan SPP",
          "account": {
            "id": "9b1e...",
            "code": "4-1001",
            "name": "Pendapatan SPP",
            "account_type": "pendapatan",
            "normal_balance": "kredit"
          }
        }
      ],
      "created_at": "2026-01-10T02:15:00+00:00",
      "updated_at": "2026-01-10T02:15:00+00:00"
    }
  ]
}
```

> **Catatan:** `balance_type` pada `details[].balance_type` selalu bernilai Inggris `"debit"` / `"credit"`.
> `account.normal_balance` (properti akun COA, bukan baris template) berbahasa Indonesia: `"debit"` /
> `"kredit"`. Dua field yang mirip tapi beda bahasa ini memang berasal dari kolom database yang berbeda —
> jangan disamakan saat parsing di sisi aplikasi lain.

### 3.2 `GET /api/journal-templates/{id}`

Detail satu template jurnal berdasarkan UUID.

**Contoh request:**

```bash
curl -s "https://finance.tazkia.ac.id/api/journal-templates/0199f2c1-2b1a-7000-9c40-1a2b3c4d5e6f" \
  -H "X-API-Key: ${EXTERNAL_API_KEY}"
```

**Response:** sama seperti satu elemen `data[]` pada §3.1 (bukan array, langsung objek).

Jika `{id}` tidak ditemukan, Laravel route-model-binding akan mengembalikan `404` bawaan framework
(bukan amplop JSON `response_code`/`response_message` — ini perlu diseragamkan jika endpoint ini
dipakai serius oleh pihak eksternal, lihat §5).

## 4. API Kirim Jurnal dari Aplikasi Lain (RANCANGAN)

> ⚠️ Bagian ini adalah **spesifikasi rancangan**, endpoint belum ada di `routes/api.php`. Tujuannya
> supaya tim aplikasi lain (mis. SPMB) bisa mulai menyiapkan payload sesuai kontrak ini, sambil endpoint
> aslinya dikerjakan menyusul (item TODO *"API POST jurnal"*).

### 4.1 Konsep & alur

1. Aplikasi lain memanggil §3.1 untuk mendapatkan `code` template dan urutan (`sequence`) tiap baris
   beserta `balance_type`-nya (debit/kredit sudah ditentukan oleh template — aplikasi pengirim tidak
   perlu tahu chart of account).
2. Aplikasi lain mengirim POST ke `/api/journal-entries` dengan kode template + tanggal transaksi +
   **nominal per baris**, mengikuti urutan `sequence` template tersebut.
3. `tazkia-finance-apps` mencocokkan nominal ke baris template sesuai urutan, menghitung total debit
   vs kredit, dan menolak jika tidak balance.
4. Jika balance, jurnal disimpan sebagai `JournalEntry` baru dengan `source_type = 'api'` (agar bisa
   ditelusuri asalnya, sama seperti pola `source_type = 'beginning_balance'` yang sudah dipakai fitur
   Saldo Awal), referensi otomatis format `JU-{YYYYMM}-####` (fungsi `JournalEntry::generateReference()`
   yang sudah ada), dan langsung berstatus **`posted`** (bukan `draft`) — karena pemanggil API dianggap
   sistem tepercaya yang sudah memvalidasi transaksinya sendiri di sisi mereka, tidak melalui alur
   review manual staf akunting seperti input lewat UI.

### 4.2 `POST /api/journal-entries`

**Header:**

```
Content-Type: application/json
X-API-Key: <EXTERNAL_API_KEY>
```

**Body request:**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `template_code` | string | ✅ | Kode template jurnal (`journal_templates.code`), lihat §3.1 |
| `organization_code` | string | ✅* | Kode organisasi (`organizations.code`). Boleh pakai `organization_id` (uuid) sebagai alternatif |
| `entry_date` | string (`YYYY-MM-DD`) | ✅ | Tanggal transaksi |
| `description` | string, maks 500 | ✅ | Deskripsi jurnal, tampil di menu Jurnal Umum |
| `external_reference` | string, maks 100 | disarankan | ID transaksi di sistem pengirim (mis. nomor kwitansi SPMB). Disimpan agar jurnal bisa ditelusuri balik ke sistem asal; **juga dipakai sebagai idempotency key** — jika `template_code` + `external_reference` yang sama dikirim ulang, API mengembalikan jurnal yang sudah ada alih-alih membuat duplikat |
| `amounts` | array of number, ≥ 0 | ✅ | Nominal per baris template, **berurutan sesuai `sequence`** hasil GET §3.1. Panjang array harus sama dengan jumlah baris template. Isi `0` pada indeks tertentu untuk melewati baris itu (mis. baris opsional) |
| `attachment_url` | string, maks 255 | opsional | Tautan bukti/lampiran (disimpan sebagai referensi, bukan diunggah lewat endpoint ini) |

`*` — kirim salah satu dari `organization_code` atau `organization_id`.

**Contoh request** (2 baris: debit Bank, kredit Pendapatan SPP, masing-masing Rp 2.500.000):

```bash
curl -s -X POST "https://finance.tazkia.ac.id/api/journal-entries" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ${EXTERNAL_API_KEY}" \
  -d '{
        "template_code": "JT-SPP-001",
        "organization_code": "TZK",
        "entry_date": "2026-07-22",
        "description": "Pembayaran SPP an. Budi Santoso — Semester Ganjil 2026/2027",
        "external_reference": "SPMB-TRX-000123",
        "amounts": [2500000, 2500000]
      }'
```

**Contoh response sukses — `200 OK`:**

```json
{
  "response_code": "200",
  "response_message": "Jurnal berhasil disimpan",
  "data": {
    "journal_entry_id": "0199f8a0-....",
    "reference": "JU-202607-0031",
    "status": "posted",
    "entry_date": "2026-07-22",
    "total_debit": 2500000,
    "total_credit": 2500000,
    "lines": [
      { "sequence": 0, "account_code": "1-1002", "account_name": "Bank BSI 7888866641", "debit": 2500000, "credit": 0 },
      { "sequence": 1, "account_code": "4-1001", "account_name": "Pendapatan SPP", "debit": 0, "credit": 2500000 }
    ]
  }
}
```

**Contoh response gagal — tidak balance (`400`):**

Ini terjadi kalau jumlah `amounts[]` tidak menghasilkan total debit = total kredit — misalnya template
punya lebih dari 2 baris dengan kombinasi debit/kredit yang tidak simetris dan nominal yang dikirim salah
hitung di sisi pengirim.

```json
{
  "response_code": "400",
  "response_message": "Jurnal tidak balance (selisih Rp 500.000).",
  "data": null
}
```

**Contoh response gagal — template tidak ditemukan (`404`):**

```json
{
  "response_code": "404",
  "response_message": "Template jurnal 'JT-SPP-999' tidak ditemukan atau tidak aktif.",
  "data": null
}
```

### 4.3 Aturan validasi & error

| Aturan | `response_code` | Pesan |
|---|---|---|
| `template_code` tidak ditemukan, atau `is_active = false` | 404 | "Template jurnal tidak ditemukan atau tidak aktif." |
| Organisasi pada `organization_code`/`organization_id` tidak ditemukan | 404 | "Organisasi tidak ditemukan." |
| Template bukan milik organisasi yang diminta | 403 | "Template tidak berlaku untuk organisasi ini." |
| Panjang `amounts[]` ≠ jumlah baris template | 422 | "Jumlah nominal harus sama dengan jumlah baris template (n baris)." |
| Ada nilai `amounts[]` negatif | 422 | "Nominal tidak boleh negatif." |
| Semua `amounts[]` bernilai 0 | 422 | "Isi minimal satu baris dengan nominal lebih dari 0." |
| Total debit ≠ total kredit (toleransi pembulatan 0.01) | 400 | "Jurnal tidak balance (selisih Rp {selisih})." — pola sama seperti validasi di `JournalEntryController::validateLines()` dan `BeginningBalanceController::save()` yang sudah ada |
| `template_code` + `external_reference` sama persis dengan request sebelumnya yang sukses | 200 | Bukan error — kembalikan jurnal yang sudah tersimpan (idempotent), tidak membuat entri baru |
| Akun pada baris template sudah dihapus/nonaktif sejak template dibuat | 422 | "Akun {kode} pada baris {sequence} sudah tidak aktif, hubungi tim akunting." |

### 4.4 Skenario penuh — integrasi SPMB

1. Sistem SPMB memanggil `GET /api/journal-templates?organization_code=TZK&category=SPP` sekali di
   awal (atau cache berkala), menyimpan `code` = `JT-SPP-001` dan tahu ada 2 baris: sequence 0 = debit
   Bank, sequence 1 = kredit Pendapatan SPP.
2. Mahasiswa bayar SPP Rp 2.500.000 lewat sistem SPMB, sistem SPMB mencatat transaksinya sendiri
   dengan nomor `SPMB-TRX-000123`.
3. Sistem SPMB memanggil `POST /api/journal-entries` seperti contoh §4.2, mengirim
   `amounts: [2500000, 2500000]` (nilai sama karena dua baris sama-sama merepresentasikan nominal
   penuh pembayaran — debit di kas/bank, kredit di pendapatan).
4. `tazkia-finance-apps` membalas `200` dengan `reference: "JU-202607-0031"`. Sistem SPMB menyimpan
   `reference` ini sebagai bukti pencatatan sudah masuk ke jurnal akunting.
5. Jurnal langsung muncul di menu **Jurnal Umum** (status *posted*) dan otomatis terhitung di
   **Buku Besar** (`/reports/buku-besar`) akun Bank & Pendapatan SPP terkait, tanpa staf akunting perlu
   input manual.
6. Jika SPMB memanggil ulang endpoint yang sama dengan `external_reference` yang sama (misalnya karena
   retry akibat timeout jaringan), API mengembalikan jurnal `JU-202607-0031` yang sama — bukan
   entri dobel.

## 5. Catatan implementasi (untuk dikerjakan tim `tazkia-finance-apps`)

Poin-poin ini perlu diputuskan/dikerjakan saat endpoint POST di §4 benar-benar dibangun:

1. **Route** — tambahkan ke `routes/api.php` di dalam grup `Route::middleware('api.key')`:
   `Route::post('journal-entries', [JournalEntryApiController::class, 'store']);`
2. **Controller baru** — `App\Http\Controllers\Api\JournalEntryApiController`, meniru pola validasi
   balance yang sudah ada di `BeginningBalanceController::save()` (toleransi `abs($totalDebit - $totalCredit) > 0.01`).
3. **`source_type`** — set `'api'` pada `JournalEntry` yang dibuat, konsisten dengan kolom
   `source_type`/`source_id` yang sudah ditambahkan migrasi `2026_07_13_000003_add_source_to_journal_entries_table.php`
   untuk fitur Saldo Awal (`'beginning_balance'`).
4. **Idempotensi (`external_reference`)** — butuh kolom baru (mis. `journal_entries.external_reference`,
   unique bersama `organization_id`) kalau mau benar-benar dicek di database, bukan cuma disimpan di
   `description`. Ini belum ada di skema saat ini.
5. **Multi-key per partner** — saat ini `EXTERNAL_API_KEY` satu untuk semua. Kalau butuh mencabut akses
   satu aplikasi tanpa mengganggu yang lain, perlu tabel `api_clients` (nama, key, organisasi yang boleh
   diakses) menggantikan `VerifyApiKey` yang sekarang membandingkan ke satu key statis di `.env`.
6. **Format error 404 route-model-binding** (§3.2) — saat ini `GET /api/journal-templates/{id}` dengan id
   tak ditemukan akan menghasilkan halaman 404 default Laravel, bukan JSON `response_code`/`response_message`
   yang konsisten. Sebaiknya diseragamkan bersamaan saat endpoint POST dibangun.

## 6. Riwayat perubahan dokumen

| Tanggal | Perubahan |
|---|---|
| 2026-07-22 | Draf awal — dokumentasi API Template Jurnal (live) + rancangan API POST Jurnal (belum dibangun) |
