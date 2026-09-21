# LATEKAJE v3 — Inventaris & Peminjaman Alat Lab

Rebuild dari aplikasi LATEKAJE lama. Laravel 13 + Filament 5.
Fokus v3: integrasi data yang benar (stok–kondisi–lokasi sinkron),
tambah unit massal, scanner QR webcam, dan pengembalian ber-PIN.

## Fitur

- **Aset** (katalog/grup alat) + **Data Unit/QR** (unit fisik berkode unik)
- **Tambah unit massal**: isi jumlah → kode `LTKJ-YYYY-#####` digenerate otomatis;
  tempel SN manual (satu per baris) bila alat punya SN
- **Import Excel/CSV**: mode satuan (per SN) atau massal (`jumlah` + `lokasi`);
  template CSV (titik-koma, ramah Excel Indonesia) bisa diunduh dari tabel
- **Peminjaman**: scan QR webcam / ketik manual, validasi stok-kondisi real-time,
  PIN 6 digit ditentukan saat meminjam
- **Pengembalian**: verifikasi QR + PIN + nama pengembali (sendiri/wakil) +
  penerima petugas (opsional) + foto bukti (opsional)
- **Master Lokasi & Kelas** (dikelola superadmin/toolman, dropdown dinamis)
- **Cetak stiker QR** satuan/massal, **dashboard** stat + donat + bar + tabel operasional
- **Role**: superadmin, toolman, anak_pkl, siswa (mode kiosk)

## Syarat

PHP ^8.3 (ext: bcmath, ctype, fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml, gd),
Composer 2, MySQL/MariaDB, Node 20 (opsional, hanya bila ubah aset Vite).

## Instalasi

```bash
cp .env.example .env
composer install
php artisan key:generate
# sesuaikan DB_* , APP_URL, SUPERADMIN_* di .env
php artisan migrate --seed
php artisan storage:link
```

Jalankan dev: `php artisan serve --host=0.0.0.0 --port=8002`
(produksi: arahkan web server ke `public/`, pasang HTTPS valid —
kamera QR wajib secure context).

## Format Import CSV

Kolom: `nama_alat, kode_aset, jenis, spesifikasi, kegunaan,
nomor_seri_atau_qr, kondisi, lokasi, status, jumlah`

- `nama_alat` wajib. `nomor_seri_atau_qr` terisi = mode satuan.
- Tanpa SN + `jumlah=N` = mode massal (N unit berkode otomatis).
- `lokasi` baru otomatis dibuatkan master. `kondisi`: baik/rusak/rusak_total.

## Keamanan

- Jangan commit `.env`, `*.bak`, `ssl-v2/`, `*.log`, dump `*.sql(.gz)` (sudah di `.gitignore`).
- Kredensial superadmin awal HANYA via env `SUPERADMIN_*` (lihat `.env.example`).
- Ganti `APP_KEY` + password DB/superadmin di tiap environment baru.
