# Changelog — Centaurian FunRun API

Semua perubahan penting pada API dicatat di sini.

Format: [Keep a Changelog](https://keepachangelog.com/)

---
## [1.1.0] - 2026-09-30

### Added
- Endpoint publik `GET /categories` & `GET /packages`.
- Auto `payment_status = free` untuk paket gratis.
- Endpoint upload bukti bayar: `POST /registrations/{no}/payment-proof`.
- Endpoint cek status pendaftaran: `POST /registrations/check-status`.
- CRUD Event oleh admin: `GET/POST/PUT/DELETE /admin/events`, `PUT /admin/events/{id}/toggle-active`.
- Notifikasi email verifikasi otomatis (`RegistrationVerifiedMail`).
- Activity log otomatis via trait `LogsActivity`.
- Endpoint admin lihat scan logs: `GET /admin/scan-logs`, `GET /admin/scan-logs/{id}`.
- CRUD Settings: `GET/POST/PUT/DELETE /admin/settings`.
- Export data CSV: `GET /admin/export/participants`, `GET /admin/export/registrations`.
- Endpoint admin lihat activity logs: `GET /admin/activity-logs`, `GET /admin/activity-logs/{id}`.
- Endpoint reset acara: `POST /admin/events/{id}/reset` (dengan konfirmasi + password).

### Changed
- API selalu balas JSON saat unauthenticated (tidak redirect ke login).
- `EventResource` menambahkan `created_at` & `updated_at`.

### Security
- Reset acara butuh konfirmasi `"RESET"` + password admin.
- Activity log mencatat IP & user agent.

### Tests
- Menambahkan 100+ automated test untuk fitur baru.
- Total: **163 test** semuanya lolos.

## [1.0.0] — 2026-09-29

### Added
- Autentikasi dengan Laravel Sanctum (login, logout, /me).
- Rate limiting login (5x/menit).
- CORS untuk frontend (localhost:3000, localhost:5173).
- Endpoint Website Utama: event aktif, konten, registrasi, time result.
- Endpoint Website Panitia: rekap kategori, rekap peserta, start acara, scan barcode.
- Endpoint Website Admin: CRUD konten, collect data, kelola user.
- Penomoran peserta otomatis per kategori (format `SW-0001`).
- Barcode unik per pendaftaran.
- Pencatatan log scan (`scan_logs`).
- Activity log (struktur tersedia).
- Rate limiter login.

### Changed
- Kolom `registration_number`, `sequence_number`, `barcode` dipindah dari `participants` ke `registrations`.
- Validasi email: 1 email boleh daftar banyak event, tapi 1x per event.
- `duration` dihitung dengan `getTimestamp()` agar selalu positif.

### Removed
- Migrasi bawaan Laravel: `cache`, `jobs`, `failed_jobs` (tidak dipakai).
- UNIQUE constraint pada `participants.email`.

### Fixed
- `ResultResource` mengakses `registration_number` dari `registration`, bukan `participant`.
- `ResultController@show` mencari berdasarkan `registration_number` di `registrations`.
- `DataController@participants` tidak lagi `orderBy('registration_number')`.

### Security
- Password di-hash dengan `Hash::make`.
- Token Sanctum untuk API.
- Middleware role (`admin` / `panitia`).
- Admin tidak bisa hapus akun sendiri.
- Rate limiting login.