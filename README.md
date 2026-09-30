# Centaurian FunRun — Backend API

Backend API untuk kegiatan **Centaurian FunRun** — terdiri dari 3 website
(Utama, Panitia, Admin) dengan satu backend terpusat.

## Teknologi

- Laravel 13
- PHP 8.3
- MySQL 8.4
- Laravel Sanctum (API Token)
- PHPUnit (163 test)

## Fitur Utama

- Pendaftaran peserta dengan nomor otomatis & barcode unik
- Scan barcode di garis finis dengan deteksi duplikat
- Start acara serentak (tombol start)
- Perhitungan waktu tempuh otomatis
- Upload bukti bayar
- Cek status pendaftaran (publik)
- Verifikasi pendaftaran + notifikasi email
- CRUD konten, event, user (admin)
- Audit trail (activity log)
- Scan logs
- Export data CSV
- Reset acara dengan konfirmasi + password

## Instalasi Lokal

```bash
git clone https://github.com/username/centaurian-funrun-backend.git
cd centaurian-funrun-backend
composer install
cp .env.example .env
php artisan key:generate
# Edit .env sesuai konfigurasi database Anda
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve