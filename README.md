# LATEKAJE v3 — Inventaris & Peminjaman Alat Lab

Sistem informasi inventaris dan sirkulasi peminjaman alat laboratorium (studi kasus: Lab TJKT SMK).
Dibangun dengan **Laravel 13 + Filament 5** (panel admin), MySQL/MariaDB, QR Code, WebSocket (Reverb),
dan notifikasi WhatsApp (GOWA) + webhook.

> **Status:** aktif dikembangkan di branch `latekaje-v3`, stabil di `main`.
> Dokumentasi ini menjelaskan **apa** aplikasinya, **bagaimana** menjalankannya,
> dan **bagaimana menyelesaikan masalah** yang umum ditemui (lihat [Pemecahan Masalah](#-pemecahan-masalah-troubleshooting)).

---

## Daftar Isi

1. [Ringkasan & Alur Kerja](#1-ringkasan--alur-kerja)
2. [Fitur per Modul](#2-fitur-per-modul)
3. [Hak Akses & Keamanan](#3-hak-akses--keamanan)
4. [Persyaratan Sistem](#4-persyaratan-sistem)
5. [Instalasi Fresh Deploy](#5-instalasi-fresh-deploy)
6. [Konfigurasi (.env)](#6-konfigurasi-env)
7. [Struktur Database](#7-struktur-database)
8. [Operasional Harian](#8-operasional-harian)
9. [Pemecahan Masalah (Troubleshooting)](#-pemecahan-masalah-troubleshooting)
10. [Testing](#10-testing)
11. [Alur Kerja Git & Deploy](#11-alur-kerja-git--deploy)

---

## 1. Ringkasan & Alur Kerja

Alur inti aplikasi hanya dua transaksi:

```
PINJAM (3 langkah di form kiosk)
  1. Scan QR alat (webcam) atau ketik kode manual → sistem validasi
     (terdaftar? tidak sedang dipinjam? kondisi baik?)
  2. Pilih siswa (ketik nama/NIS) → NIS + nama + kelas terisi otomatis
  3. Tentukan PIN 6 digit → catat/foto PIN ini

KEMBALI (modal aksi di tabel Peminjaman)
  Scan QR + PIN + nama pengembali (sendiri/di Wakilkan)
  + penerima petugas (opsional) + foto bukti (opsional)
```

**Aturan integritas yang ditegakkan sistem (bukan sekadar di UI):**

| Aturan | Penegakan |
|---|---|
| 1 unit = maks 1 pinjaman aktif | Validasi form + `LoanService` (transaksi + row lock) + constraint unik DB (`active_item_id` generated) |
| Unit rusak tidak bisa dipinjam | Validasi form + service |
| Hapus unit ber-riwayat | Riwayat dipertahankan (`asset_item_id` di-set NULL, tampil "(unit dihapus)") |
| Hapus unit yang sedang dipinjam | Ditolak policy |
| PIN salah / QR tidak cocok | Penolakan atomik, status tidak berubah setengah jalan |
| Akun nonaktif | Ditolak saat login + sesi aktif ditendang |

---

## 2. Fitur per Modul

### Dashboard
Stat (Total Unit, Siap Pakai, Dipinjam, Karantina), donat Kondisi & Lokasi,
bar aktivitas 6 bulan, tabel Pinjaman Aktif Terkini, Unit Perlu Perhatian,
dan **Belum Kembali** (semua pinjaman aktif + tombol Kirim Rekap).

### Aset (katalog/grup alat)
Grup logis alat (cth: Router Mikrotik). Aksi **Tambah Unit** per baris:
isi jumlah → kode `LTKJ-YYYY-#####` digenerate otomatis dan berurutan;
tempel SN manual (satu per baris) bila alat punya SN pabrik; campuran keduanya bisa.

### Data Unit/QR
Unit fisik. Form create mendukung **jumlah massal + SN manual**.
Header tabel: **Import**, **Template CSV**, **Unduh Excel**, **Cetak Semua QR**.
Aksi baris dikelompokkan dalam dropdown (ramah mobile).
Import dua mode: satuan (per SN) atau massal (`jumlah` + `lokasi`).

### Peminjaman & Pengembalian
Lihat alur di atas. Siswa login pribadi **langsung diarahkan ke halaman Peminjaman**
dan hanya melihat pinjamannya sendiri; akun kiosk generik (tanpa NIS) melihat semua.

### Master: Lokasi, Kelas, Siswa
Dikelola superadmin/toolman (dropdown di form otomatis mengikuti).
Siswa: NIS unik, import CSV + template, tombol **Buat Akun** per baris
(login = NIS, password awal = NIS, wajib diganti di Profil).

### Kelola User
Filter role, kolom NIS + data siswa tertaut, ubah password, toggle
**Akun aktif** (blokir login tanpa hapus akun), hapus, dan **ACL khusus per user**
(izinkan/larang 20 hak individual yang menimpa role — khusus superadmin).

### Setting (khusus superadmin/toolman)
- **Aplikasi:** Mode Perawatan (dengan modal konfirmasi, superadmin tetap bisa masuk).
- **API:** buat/cabut token Sanctum (Bearer) untuk integrasi luar.
- **Notifikasi:** GOWA WhatsApp (direct / via relay bila server tak sejaringan),
  webhook umum (5 mode auth: header, bearer, basic, query, none), jadwal rekap,
  tombol tes koneksi + kirim pesan tes, riwayat pengiriman.
- **Rekap Harian Otomatis:** scheduler `recap:unreturned --send` tiap jam
  yang dikonfigurasi (default 16:00) ke kanal aktif.

### Cetak QR & Export
- `/print-qr?ids=all|1,2,3` → stiker QR siap cetak.
- Export rekap Peminjaman ke **XLSX (prioritas)** / CSV — hanya superadmin,
  toolman, anak PKL.

### Realtime (opsional)
Laravel Reverb: tabel + dashboard refresh sendiri dan toast notifikasi
saat ada transaksi dari perangkat lain. Tanpa Reverb, listener diam
(tidak error) — fitur inti tetap jalan.

---

## 3. Hak Akses & Keamanan

| Kemampuan | superadmin | toolman | anak_pkl | siswa |
|---|---|---|---|---|
| Kelola User / ACL / Setting / API | ✅ | ❌ | ❌ | ❌ |
| Kelola master (Aset, Lokasi, Kelas, Siswa) | ✅ | ✅ | ➖ (unit+siswa saja) | ❌ |
| Tambah/ubah unit, hapus (bila tak ada pinjaman aktif) | ✅ | ✅ | ➖ (tanpa hapus) | ❌ |
| Pinjam untuk siapa pun | ✅ | ✅ | ✅ | 🔒 milik sendiri |
| Kembalikan + Export rekap | ✅ | ✅ | ✅ | ❌ |
| Lihat pinjaman | semua | semua | semua | milik sendiri |

- Login: **NIS atau email** + password (`NisUserProvider`).
- Password: hashing bcrypt; password default NIS **wajib diganti** di Profil.
- Kredensial awal **hanya** via env `SUPERADMIN_*` (seeder menolak password default
  bila env kosong → dibuat acak + peringatan).
- File yang tidak boleh masuk git: `.env`, `*.bak`, `ssl-v2/`, `*.log`,
  dump `*.sql(.gz)`, `run-*.sh`, compiled view Blade (semua sudah di `.gitignore`).

---

## 4. Persyaratan Sistem

- PHP `^8.3` — ekstensi: bcmath, ctype, fileinfo, mbstring, openssl,
  pdo_mysql, tokenizer, xml, gd (+ pdo_sqlite bila dev lokal pakai SQLite)
- Composer 2, MySQL ≥ 8 / MariaDB ≥ 10.4, Node 20 (opsional — hanya bila
  mengubah aset Vite; UI custom memakai `public/css/latekaje.css` tanpa build)
- HTTPS valid di production — **kamera QR wajib secure context**
  (self-signed bisa untuk staging/dev, browser: Advanced → Proceed)

---

## 5. Instalasi Fresh Deploy

```bash
cp .env.example .env
composer install
php artisan key:generate
# --- isi .env: DB_*, APP_URL (https://...), SUPERADMIN_* ---
php artisan migrate --seed
php artisan storage:link
```

Layanan web arahkan ke `public/`. Scheduler (rekap harian) — cron tiap menit:

```bash
* * * * * php /path/to/artisan schedule:run >> schedule.log 2>&1
```

Environment opsional:

| Kebutuhan | Cara |
|---|---|
| WebSocket realtime | `BROADCAST_CONNECTION=reverb` + isi `REVERB_*`, jalankan `php artisan reverb:start --host=127.0.0.1 --port=8081` (+ terminasi TLS bila perlu) |
| Tanpa data demo | `SEED_DEMO=false` |
| Satu DB dipakai bersama app lain | `DB_PREFIX=v3_` (atau kosongkan bila DB khusus) |

---

## 6. Konfigurasi (.env)

Yang wajib diperhatikan (selengkapnya lihat `.env.example`):

```env
APP_URL=https://domain-anda          # HARUS https publik di produksi
APP_LOCALE=id
DB_CONNECTION=mysql
DB_DATABASE=latekaje
DB_PREFIX=                           # kosongkan bila DB khusus app ini
SUPERADMIN_NAME="admin"
SUPERADMIN_EMAIL="admin@latekaje.net"
SUPERADMIN_PASSWORD="Ganti-Yang-Kuat!"   # WAJIB diganti per environment
SEED_DEMO=false                          # false untuk produksi
```

> `.env.example` adalah satu-satunya acuan resmi. Jangan pernah commit `.env`
> asli — repo ini hanya berisi `.env.example` yang aman.

---

## 7. Struktur Database

Entitas inti: `users` ⇄ `students` → `loans` ⇄ `asset_items` → `assets` (+ `locations`).

- `assets` — katalog (kode_aset unik, nama, jenis, spesifikasi, kegunaan).
- `asset_items` — unit fisik (`nomor_seri_atau_qr` unik, status tersedia/dipinjam,
  kondisi baik/rusak/rusak_total, `location_id`). Kolom `stok` diabaikan
  (total dihitung real-time, bukan disimpan).
- `loans` — transaksi (`asset_item_id` nullable + nullOnDelete agar riwayat utuh,
  `nis`, `student_id`, `return_pin`, `returned_by`, `return_relation`,
  `return_method`, `received_by`, `return_photo_path`, kolom guard unik
  `active_item_id` = maks 1 pinjaman aktif per unit).
- `students` (`nis` unik), `locations`, `school_classes`, `settings` (key-value),
  `recap_logs`, `personal_access_tokens` (Sanctum), tabel bawaan
  Filament (imports/exports) + Laravel (sessions/cache/jobs).

---

## 8. Operasional Harian

| Tugas | Cara |
|---|---|
| Tambah 10 unit sejenis | Tabel Aset → **Tambah Unit** → isi jumlah (+ SN bila ada) |
| Daftarkan siswa baru/rombongan | Menu Siswa → **Template CSV** → isi → **Import** (kolom `password` opsional) |
| Buatkan login siswa | Baris siswa → **Buat Akun** (info NIS + password awal tampil sekali) |
| Rekap belum kembali | Widget **Belum Kembali** / perintah `php artisan recap:unreturned` (`--send` untuk kirim) |
| Blokir akun bermasalah | Kelola User → toggle **Akun aktif** / tombol Nonaktifkan |
| Tutup darurat | Setting → **Mode Perawatan** (konfirmasi dulu; superadmin tetap bisa masuk) |
| Cek kesehatan | `php artisan test` (skipped bila data test tak ada — normal) |

---

## 9. Pemecahan Masalah (Troubleshooting)

| Gejala | Penyebab umum → Solusi |
|---|---|
| Kamera tidak meminta izin / tidak tampil | Halaman harus **HTTPS** (self-signed: Proceed dulu). Cek tombol *Scan Ulang*; di HP pakai browser Chrome. Preview digambar mirror-off agar gerakan natural. |
| `touch(): Utime failed` / error tulis `storage/` | File compiled-view/log milik user berbeda (habis jalan artisan sebagai user lain). Solusi: hapus file compiled milik user lain di `storage/framework/views/`, pastikan grup `www-data` bisa tulis. **Jangan** jalankan test/artisan perender di folder produksi. |
| Import CSV menumpuk di kolom A (Excel Indonesia) | Excel ID memakai pemisah **titik-koma**. Unduh **Template CSV** dari tabel (sudah `;` + BOM UTF-8); uploader otomatis mendeteksi `,`/`;`/tab. |
| `SQLSTATE ... generated column` / `errno: 150` saat migrate fresh | Kolom guard `active_item_id` butuh sintaks CASE (portabel) dan `asset_item_id` nullable — sudah diperbaiki di migrasi bawaan repo ini. Jangan edit manual. |
| Login GOWA 401 / kirim gagal | Cek base URL **tanpa path** (cth: `http://host:3000`), user+pass basic auth, dan device ter-pairing. Bila server tak sejaringan dengan GOWA, isi **Relay URL + Secret**. Pesan error kini berlabel `[via relay]`/`[direct]` agar jelas jalurnya. |
| Webhook 400 `phone cannot be blank` | URL webhook diarahkan ke endpoint GOWA (`/send/message`). Webhook Umum hanya untuk penerima generik (n8n/bot). Untuk WA pakai bagian GOWA. |
| Redirect ke `http://` setelah login | `APP_URL` harus `https://...` + `trustProxies` aktif; di belakang proxy pastikan header `X-Forwarded-Proto` diteruskan. |
| Tabel kosong padahal data ada | Cek `DB_PREFIX` (salah prefix = baca tabel чужой), dan role user (siswa hanya melihat miliknya). |
| Scheduler rekap tidak jalan | Pastikan cron `schedule:run` terpasang dan `recap_enabled=1` + kanal (GOWA/webhook) aktif di Setting. Lihat **Riwayat Pengiriman**. |
| `419 Page Expired` di form | Sesi habis / `APP_KEY` berubah — login ulang. |

---

## 10. Testing

```bash
php artisan test
```

Cakupan: smoke 10 halaman admin, login NIS + scope pribadi, regresi kiosk
generik, redirect role, keamanan akun (blokir login, ACL deny, token),
listener WS, redirect root. Test yang butuh data spesifik akan **skip**
(secara eksplisit, bukan gagal) bila data tak ada.

---

## 11. Alur Kerja Git & Deploy

- Branch: `latekaje-v3` (pengembangan) → merge `--no-ff` ke `main` (stabil/prod).
- Pola: edit di mirror → commit → push → deploy rsync ke staging
  (kecualikan `.env`, `storage/`, `vendor/`, `ssl-v2/`, `run-*.sh`)
  → post-deploy: `composer dump-autoload -o`, `migrate --force`,
  `view:clear`, `config:clear`, test, hapus compiled view milik user deploy.
- Jangan pernah menjalankan test/artisan perender-view di folder produksi
  (lihat troubleshooting `Utime failed`).
