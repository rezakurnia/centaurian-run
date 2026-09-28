# Centaurian-Funrun

Backend REST API untuk Centaurian Fun Run: Laravel 13 (PHP ^8.3), auth pakai token Sanctum.

## Tim & Branch

| Siapa | Bagian | Branch |
|-------|--------|--------|
| Reza (`rezakurnia`) | Frontend | `frontend` |
| Ilham (`ilham-gif-lab`) | Backend | `backend` |

`main` = versi gabungan yang sudah jalan. Jangan kerja langsung di `main`.

## Workflow

Pertama kali (ganti `frontend` dengan `backend` untuk Ilham):

```sh
git fetch origin
git checkout frontend
```

Setiap hari:

```sh
git pull origin main        # ambil update terbaru dari main (+ kerjaan teman yang sudah di-merge)
# ... edit file ...
git add .
git commit -m "Jelaskan perubahan"
git push
```

Kalau fitur sudah jalan: buka GitHub → **Pull requests** → **New pull request** → pilih `main` ← `frontend` (atau `backend`) → **Create** → **Merge**.

Kalau `git pull` bilang ada *conflict*: buka file yang ditandai, pilih versi yang benar, lalu `git add .` dan `git commit`.

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
