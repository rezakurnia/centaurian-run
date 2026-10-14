# 🏃‍♂️ Centaurian Run — Dokumentasi Lengkap Proyek

> **Centaurian Run** (Centaurian FunRun) adalah platform digital terintegrasi untuk kegiatan Fun Run keluarga besar Yayasan / SMA Alfa Centauri (siswa, guru, staf, alumni, orang tua, dan masyarakat umum). 
> Sistem ini menggabungkan landing page publik berstandar editorial interaktif tingkat tinggi (berbasis *MindMarket style reference*), sistem registrasi & verifikasi pembayaran, scan barcode garis finish panitia, serta dashboard manajemen terpusat bagi administrator.

---

## 📌 1. Ikhtisar & Arsitektur Sistem

Aplikasi ini menggunakan arsitektur **Backend API Monolith Terpusat** berbasis Laravel yang melayani 3 antarmuka pengguna:
1. **Website Publik (Utama):** Landing page interaktif berdesain *warm storybook on cream paper*, formulir pendaftaran peserta, unggah bukti bayar, pengecekan status registrasi, dan papan hasil lomba (*leaderboard*).
2. **Portal Panitia (Operations):** Antarmuka operasional lapangan untuk memicu waktu *start* serentak, memindai barcode/QR peserta di garis finis secara *real-time*, validasi duplikat, dan rekap peserta.
3. **Portal Admin (Management & CMS):** Pengelolaan master event, paket dan kategori, verifikasi pembayaran peserta, manajemen konten landing page (CMS), audit trail (*activity log*), log pemindaian, serta ekspor data ke CSV.

```
                     ┌────────────────────────────────────────────────────────┐
                     │           CENTAURIAN RUN — BACKEND API                 │
                     │  (Laravel 13 · PHP 8.3/8.5 · Sanctum · MySQL 8.4)       │
                     └────────────────────────┬───────────────────────────────┘
                                              │
         ┌────────────────────────────────────┼────────────────────────────────────┐
         ▼                                    ▼                                    ▼
┌──────────────────┐               ┌──────────────────┐               ┌──────────────────┐
│  Website Publik  │               │  Portal Panitia  │               │   Portal Admin   │
│  · Landing Page  │               │  · Start Timer   │               │  · Verifikasi    │
│  · Registrasi    │               │  · Scan Barcode  │               │  · Manajemen CMS │
│  · Cek Status    │               │  · Rekap Lapangan│               │  · Audit & Ekspor│
│  · Hasil Lomba   │               │  · Anti-Duplikat │               │  · Reset Acara   │
└──────────────────┘               └──────────────────┘               └──────────────────┘
```

---

## 🛠️ 2. Tech Stack & Ekosistem

### Backend
- **Framework:** Laravel 13
- **Bahasa:** PHP 8.3 / 8.5
- **Database:** MySQL 8.4
- **Autentikasi API:** Laravel Sanctum (Token-based & Role-based Access: `admin`, `panitia`)
- **Testing & Quality:** PHPUnit (Feature Tests) & Laravel Pint

### Frontend & Motion
- **CSS Framework:** Tailwind CSS v4 (`@tailwindcss/vite`)
- **Build Tool:** Vite 8
- **Animasi & Interaktivitas:**
  - **GSAP 3** (`ScrollTrigger`, `DrawSVGPlugin`, `CustomEase`)
  - **Lenis Smooth Scroll**
  - **Rive Runtime** (`@rive-app/canvas-lite` untuk animasi karakter dinamis)
  - **Web Components** (`<c-rive>`, `<c-deluxe-callout>`, `<c-homepage-timeline>`)
- **Tipografi:** Single-family system menggunakan **Inter** (via Bunny CDN Fonts)

---

## 🎨 3. Design System (MindMarket Style Reference)

Desain antarmuka publik mengadopsi filosofi **MindMarket Style Reference**: *“Warm storybook on cream paper”* dengan estetika editorial lembut, rounded radii tinggi (*sticker-like*), dan tipografi berukuran masif.

### Palet Warna (Design Tokens)
| Token | Nilai Hex | Peran / Penggunaan |
|---|---|---|
| `--color-fresh-grass` (`--color-green`) | `#8ed462` | Aksen warna brand utama, stroke navigasi, border, jalur rute, hero fill |
| `--color-cream-paper` (`--color-beige`) | `#f5f1e4` | Kanvas latar belakang utama (bukan putih murni) |
| `--color-ink-black` (`--color-charcoal`)| `#2c2e2a` | Teks utama, ikon, garis outline lembut |
| `--color-pure-white` | `#ffffff` | Permukaan kartu terangkat (*elevated cards*), bilah navigasi melayang |
| `--color-sandstone` (`--color-beige-500`) | `#e0dbce` | Surface sekunder/inset, garis pembatas halus |
| `--color-stone-gray` | `#80827f` | Teks teredam (*muted body*), secondary border |
| `--color-hairline-mist` | `#d5d5d4` | Garis struktur tipis bergradasi rendah |
| `--color-sunshine-pop` (`--color-yellow`)| `#f5e211` | Blok aksen footer bawah (*Footer Accent Block*), tombol paket |
| `--color-coral-pop` (`--color-red`) | `#ff705d` | Tombol aksi kartu Kategori / pendaftaran |
| `--color-sky-pop` (`--color-blue`) | `#2ba0ff` | Aksen tombol kartu Jadwal & elemen dekorasi |
| `--color-pink` | `#ebc1ff` | Aksen tombol kartu Hasil lomba & ornamen |

### Bentuk & Border Radius
- **Bilah Navigasi (`--radius-nav`):** `50px` (Pill mengambang)
- **Kartu Konten (`--radius-cards`):** `50px`
- **Tombol Aksi (`--radius-buttons`):** `50px` (Pill dengan lingkaran ikon responsif)
- **Tag / Chip Kategori (`--radius-small`):** `10px`
- **Container Ilustrasi (`--radius-illustration-containers`):** `63.75px`

### Karakteristik Elevasi
- **Zero Digital Drop-Shadow:** Tidak menggunakan bayangan jatuh (*drop-shadow*) pada kartu maupun navigasi; elevasi tercipta secara alami dari pergeseran warna lapisan *Cream Paper* (`#f5f1e4`) ke *Pure White* (`#ffffff`).

---

## 📂 4. Struktur Direktori Utama

```
alcent-centaurian-run/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Controller.php
│   │       └── Api/
│   │           ├── AuthController.php           # Login & Logout Sanctum
│   │           ├── CategoryController.php       # Data kategori publik
│   │           ├── ContentController.php        # Konten landing page
│   │           ├── EventController.php          # Event aktif
│   │           ├── PackageController.php        # Paket lari publik
│   │           ├── RegistrationController.php   # Registrasi, cek status, upload bukti bayar
│   │           ├── ResultController.php         # Papan hasil lomba
│   │           ├── Admin/                       # Dashboard, CRUD User/Event/Konten, Export CSV
│   │           └── Panitia/                     # Scan finish, Start timer, Rekap lapangan
│   └── Models/
│       ├── ActivityLog.php
│       ├── Category.php
│       ├── Content.php
│       ├── Event.php
│       ├── EventCategory.php
│       ├── Package.php
│       ├── Participant.php
│       ├── Registration.php
│       ├── Result.php
│       ├── ScanLog.php
│       ├── Setting.php
│       └── User.php
├── database/
│   ├── factories/                               # Factory model untuk testing
│   ├── migrations/                              # Struktur tabel database
│   └── seeders/                                 # Dummy & initial data
├── docs/                                        # Dokumentasi teknis, API.md, wireframe, Postman
├── public/
│   ├── images/                                  # Aset gambar & ilustrasi
│   ├── rive/                                    # File animasi Rive (.riv)
│   └── timeline/                                # Aset SVG interaktif timeline
├── resources/
│   ├── css/
│   │   └── app.css                              # Tailwind v4 `@theme`, tokens, komponen
│   ├── js/
│   │   ├── app.js                               # GSAP, Lenis, Web Component loader
│   │   └── components/
│   │       ├── c-rive.js                        # Web component player Rive
│   │       ├── c-deluxe-callout.js              # Interaksi pop-up callout section
│   │       └── c-homepage-timeline.js           # Penanganan jalur SVG timeline
│   └── views/
│       ├── landing.blade.php                    # Halaman depan (Landing page utama)
│       ├── emails/                              # Template email konfirmasi pendaftaran
│       └── partials/                            # Komponen Blade (seperti btn.blade.php)
├── routes/
│   ├── api.php                                  # Rute API (Public, Panitia, Admin)
│   ├── console.php                              # Task scheduling / console artisan
│   └── web.php                                  # Rute landing page publik
├── tests/
│   └── Feature/                                 # 20+ file unit & feature tests
├── DESIGN.md                                    # Spesifikasi lengkap Design System
├── CLAUDE.md / AGENTS.md                        # Panduan AI Assistant & Aturan Git Workflow
├── composer.json                                # Ketergantungan PHP
├── package.json                                 # Ketergantungan NPM & library animasi
└── vite.config.js                               # Konfigurasi bundler Vite
```

---

## ⚡ 5. Alur Fitur Utama

### 1. Pendaftaran Peserta (Public Registration)
1. Peserta memilih **Kategori** (Siswa, Guru/Karyawan, Alumni, Tamu/Umum).
2. Peserta memilih **Paket** (misal: *Gratis* atau *Berbayar* dengan benefit medali, jersey, dll.).
3. Sistem membuat data `Registration` dan `Participant`, menghasilkan:
   - Nomor registrasi unik (misal: `CR-2026-0001`).
   - Kode barcode/QR untuk check-in dan scan finis.
4. Jika berbayar, peserta mengunggah bukti transfer melalui endpoint `/payment-proof`.
5. Status pendaftaran dapat dipantau peserta melalui fitur **Cek Status**.

### 2. Hari Pelaksanaan (Race Day & Panitia)
1. **Start Acara:** Panitia membuka acara dan menekan tombol *Start* (`POST /api/panitia/start`) yang mencatat waktu mulai resmi lomba ke data `Event`.
2. **Scan Finish:** Saat pelari melintasi garis finis, barcode peserta dipindai oleh panitia (`POST /api/panitia/scan`).
   - Sistem mencatat waktu finis.
   - Perhitungan selisih waktu (`duration_seconds`) dan format waktu (`duration_formatted`).
   - Memiliki proteksi deteksi duplikasi scan (*duplicate scan protection*).
   - Menyimpan jejak audit di tabel `scan_logs`.

### 3. Papan Hasil Lomba (Live Leaderboard)
- Publik dapat melihat hasil lomba secara langsung melalui `/results`, terurut berdasarkan durasi waktu tercepat per kategori peserta.

### 4. Admin Management & Verifikasi
- Administrator memverifikasi bukti pembayaran yang masuk.
- Verifikasi memicu notifikasi email konfirmasi ke peserta secara otomatis.
- Administrator dapat mereset data acara jika dilakukan simulasi/uji coba menggunakan proteksi password.
- Ekspor seluruh data pendaftar dan peserta ke format CSV untuk arsip.

---

## 🌐 6. Ringkasan Endpoint API (`routes/api.php`)

### Public Endpoints
| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/login` | Login admin/panitia untuk mendapatkan token Sanctum (throttled) |
| `POST` | `/api/registrations` | Pendaftaran peserta baru |
| `POST` | `/api/registrations/check-status` | Mengecek status pendaftaran berdasarkan nomor registrasi |
| `POST` | `/api/registrations/{reg_no}/payment-proof` | Unggah file bukti pembayaran |
| `GET` | `/api/event/active` | Mendapatkan data event yang sedang aktif |
| `GET` | `/api/contents` | Mendapatkan data konten dinamis landing page |
| `GET` | `/api/results` | Mendapatkan daftar hasil perlombaan / leaderboard |
| `GET` | `/api/results/{reg_no}` | Mendapatkan detail hasil peserta tertentu |
| `GET` | `/api/categories` | Mendapatkan daftar kategori peserta |
| `GET` | `/api/packages` | Mendapatkan daftar paket lomba |

### Panitia Endpoints (`role:panitia,admin`)
| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/panitia/start` | Menyetel waktu start resmi acara |
| `POST` | `/api/panitia/scan` | Memindai barcode pelari di garis finish |
| `GET` | `/api/panitia/recap/category` | Rekapitulasi jumlah peserta per kategori |
| `GET` | `/api/panitia/recap/participants` | Rekapitulasi peserta berdasarkan nomor registrasi |

### Admin Endpoints (`role:admin`)
| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/admin/dashboard` | Statistik ringkasan data, pendaftaran, dan verifikasi |
| `API Resource` | `/api/admin/events` | CRUD Master Event |
| `PUT` | `/api/admin/events/{id}/toggle-active` | Mengaktifkan/menonaktifkan event |
| `POST` | `/api/admin/events/{id}/reset` | Reset seluruh data event (dengan verifikasi password) |
| `API Resource` | `/api/admin/contents` | CRUD Konten Landing Page (CMS) |
| `API Resource` | `/api/admin/users` | CRUD User admin & panitia |
| `GET` | `/api/admin/registrations` | Daftar registrasi peserta & status bayar |
| `PUT` | `/api/admin/registrations/{id}/verify` | Verifikasi status pembayaran pendaftar |
| `GET` | `/api/admin/scan-logs` | Jejak audit pemindaian finish |
| `GET` | `/api/admin/activity-logs` | Jejak aktivitas & manipulasi data |
| `GET` | `/api/admin/export/participants` | Unduh file CSV data peserta |
| `GET` | `/api/admin/export/registrations` | Unduh file CSV data registrasi |

---

## 🚀 7. Panduan Setup & Menjalankan Proyek

### Prasyarat
- **PHP:** >= 8.3 (disarankan PHP 8.5)
- **Composer:** >= 2.x
- **Node.js & NPM:** Node 20+ / NPM 10+
- **Database:** MySQL 8.0 / 8.4 atau MariaDB setara

### Langkah Instalasi
1. **Clone repositori:**
   ```bash
   git clone https://github.com/rezakurnia/centaurian-run.git
   cd centaurian-run
   ```

2. **Instalasi pustaka PHP & JavaScript:**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan konfigurasi database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) pada file `.env`.*

4. **Migrasi Database & Seed Data:**
   ```bash
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```

5. **Kompilasi Aset Frontend:**
   - Mode Development (Hot Reload):
     ```bash
     npm run dev
     ```
   - Mode Production:
     ```bash
     npm run build
     ```

6. **Menjalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## 🧪 8. Pengujian & Standarisasi Kode

- **Menjalankan PHPUnit Feature Tests:**
  ```bash
  php artisan test
  ```
- **Formatting Style PHP (Laravel Pint):**
  ```bash
  vendor/bin/pint --format agent
  ```

---

## 👥 9. Informasi Kolaborasi & Git Workflow

- **Branch Aturan:**
  - `frontend`: Dikelola oleh **Reza** untuk pengembangan UI/UX, Blade, Tailwind, Motion, dan Aset Visual.
  - `backend`: Dikelola oleh **Ilham** untuk logika API, Controller, Database, dan Integrasi Operasional.
  - `main`: Branch utama (produksi/rilis). Tidak diperkenankan *direct commit* ke `main`; integrasi dilakukan melalui Pull Request / penggabungan berkala.
