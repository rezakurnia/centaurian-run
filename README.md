# Centaurian-Funrun

Backend REST API untuk Centaurian Fun Run: Laravel 13 (PHP ^8.3), auth pakai token Sanctum.

## Workflow

Sebelum mulai kerja, selalu tarik commit terbaru:

```sh
git fetch origin
git pull --ff-only origin main
```

## Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
php artisan serve
```

Default DB = SQLite. Session, cache, dan queue memakai driver `database` (tabelnya sudah ada di migration).
Seeder membuat akun `admin@centaurian.test` (admin) dan `panitia@centaurian.test` (panitia). Password ada di `database/seeders/UserSeeder.php`.

## Endpoint (`/api`)

| Akses | Method | Path |
|-------|--------|------|
| Publik | POST | `/login` (throttle) |
| Publik | POST | `/registrations` |
| Publik | GET | `/event/active` |
| Publik | GET | `/contents` |
| Publik | GET | `/results`, `/results/{registration_number}` |
| Token | POST | `/logout` |
| Token | GET | `/me` |
| Panitia | GET | `/panitia/recap/category`, `/panitia/recap/participants` |
| Panitia | POST | `/panitia/start`, `/panitia/scan` |
| Admin | CRUD | `/admin/contents`, `/admin/users` |
| Admin | GET | `/admin/participants`, `/admin/registrations`, `/admin/registrations/{id}`, `/admin/dashboard` |
| Admin | PUT | `/admin/registrations/{id}/verify` |

Token = header `Authorization: Bearer <token>` dari `/login`. Route admin butuh `role:admin`; route panitia saat ini cukup token (belum ada cek role).
