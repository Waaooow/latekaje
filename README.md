# 💻 LATEKAJE - Sistem Peminjaman Alat Laboratorium

[![Laravel Version](https://img.shields.io/badge/Laravel-v11.x-red.svg)](https://laravel.com)
[![Filament Version](https://img.shields.io/badge/Filament-v3.x-amber.svg)](https://filamentphp.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)

**LATEKAJE** adalah aplikasi berbasis web pintar yang dirancang khusus untuk mengelola manajemen peminjaman dan pengembalian alat/aset di laboratorium sekolah. Menggunakan arsitektur modern **Laravel** dan **Filament Panel Builder v3**, sistem ini mendukung skenario penginputan cepat di lapangan (Kiosk Mode).

---

## ✨ Fitur Unggulan

*   **Skenario Akun Kiosk (Bersama):** Optimalisasi khusus untuk lab dengan keterbatasan akun individual. Siswa dapat menggunakan satu perangkat lab secara bergantian, mengetikkan nama asli secara dinamis, dan memantau datanya dengan aman melalui pencarian global.
*   **Keamanan Berlapis (Role-based Policy):** 
    *   `Superadmin`: Memiliki kendali penuh terhadap sistem dan manajemen user.
    *   `Toolman` & `Anak PKL`: Berfungsi seperti kasir; memvalidasi peminjaman dan mengubah status menjadi 'kembali'.
    *   `Siswa`: Hanya dapat membuat permohonan pinjam dan melihat riwayat tanpa hak akses manipulasi/edit data.
*   **Pencarian Pintar Lintas Relasi:** Dropdown inventaris barang mendukung pencarian langsung via *Scan QR Code*, Nomor Seri, maupun Nama Alat melalui pencarian tabel relasional.
*   **Branding Eksklusif Cyber Tech:** Integrasi visual bertema teknologi gelap dengan kustomisasi logo dan favicon *pixel-perfect*.

---

## 🛠️ Persyaratan Sistem

Sebelum melakukan instalasi, pastikan server atau lingkungan lokal Anda telah memenuhi spesifikasi berikut:
*   PHP >= 8.2 (dengan ekstensi BCMath, Ctype, Fileinfo, OpenSSL, PDO, Tokenizer, XML, GD)
*   Composer >= 2.x
*   MySQL >= 8.0 atau MariaDB >= 10.4
*   Web Server (Apache / Nginx / Artisan Development Server)

---

## 🚀 Panduan Deployment & Instalasi

Ikuti langkah-langkah di bawah ini secara berurutan untuk memasang aplikasi **LATEKAJE** di lingkungan production (Hosting/VPS) maupun lokal.

### 1. Clone atau Unggah Source Code
Dapatkan source code proyek ke dalam direktori server Anda:
```bash
git clone https://github.com/username/latekaje.git
cd latekaje
```

### 2. Instal Dependensi PHP
Jalankan Composer untuk mengunduh seluruh *library* vendor yang dibutuhkan:
```bash
composer install --no-dev --optimize-autoloader
```
*(Catatan: Hapus `--no-dev --optimize-autoloader` jika Anda menginstal untuk kebutuhan pengembangan/lokal).*

### 3. Konfigurasi Environment (`.env`)
Salin file template konfigurasi bawaan Laravel:
```bash
cp .env.example .env
```
Buka file `.env` yang baru dibuat menggunakan text editor (seperti `nano` or VS Code), kemudian sesuaikan baris-baris krusial berikut:

```env
APP_NAME=LATEKAJE
APP_ENV=production
APP_DEBUG=false
APP_URL=http://domain-anda.com

# Zona Waktu Indonesia (WIB)
APP_TIMEZONE=Asia/Jakarta

# Konfigurasi Database Anda
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=username_database_anda
DB_PASSWORD=password_database_anda

# Setup Otomatis Akun Kunci Superadmin (Seeder)
SUPERADMIN_NAME="Super Admin LATEKAJE"
SUPERADMIN_EMAIL="superadmin@latekaje.com"
SUPERADMIN_PASSWORD="PasswordKuatMilikAnda123!"
```

### 4. Generate Application Key
Buat kunci enkripsi unik untuk keamanan aplikasi Anda:
```bash
php artisan key:generate
```

### 5. Strukturisasi Database & Injeksi Data Awal (Seeding)
Jalankan perintah ini untuk membangun seluruh tabel database sekaligus membuat akun **Superadmin** pertama secara otomatis sesuai data kredensial di file `.env` Anda:
```bash
php artisan migrate --seed
```

### 6. Hubungkan Storage Link
Pastikan berkas publik dan gambar logo dapat diakses dengan baik oleh sistem Filament:
```bash
php artisan storage:link
```

### 7. Optimalisasi Cache Server (Wajib di Production)
Biar performa aplikasi sat-set dan responsif saat diakses banyak siswa secara bersamaan di lab, bersihkan dan buat cache konfigurasi baru:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔑 Informasi Akses Masuk Default

Setelah seluruh proses di atas berhasil, rute utama (`/`) akan otomatis mengalihkan pengguna ke halaman login Filament di rute `/admin`.

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Superadmin** | *Sesuai `SUPERADMIN_EMAIL` di `.env`* | *Sesuai `SUPERADMIN_PASSWORD` di `.env`* | Akses penuh & Manajemen User |
| **Siswa (Kiosk)** | `siswa@latekaje.com` *(Buat via Superadmin)* | *Ditentukan saat pembuatan* | Request Pinjam & Lihat Tabel |

> **PERINGATAN KEAMANAN:** Demi keamanan sistem di lingkungan live, segera masuk menggunakan akun Superadmin Anda dan ganti password berkala, serta pastikan status `APP_DEBUG` di file `.env` bernilai `false`.

---
Made with 💻 by **Tim Developer LATEKAJE**