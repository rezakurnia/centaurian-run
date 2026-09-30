# LAPORAN PROJECT BACKEND
# CENTAURIAN FUN RUN

**Tanggal Laporan:** 30 September 2026
**Status:** Backend 100% Selesai & Teruji
**Versi:** 1.1.0
**Disusun oleh:** Tim Backend Centaurian FunRun

---

## DAFTAR ISI

1. Ringkasan Project
2. Teknologi yang Digunakan
3. Struktur Database
4. Daftar Endpoint API
5. Statistik Testing
6. Fitur Unggulan
7. Alur Proses Bisnis
8. Catatan Teknis
9. Status Akhir

---

## 1. RINGKASAN PROJECT

Centaurian FunRun adalah kegiatan lari santai yang melibatkan
peserta dari berbagai kalangan: siswa, guru & karyawan, alumni,
orangtua, dan peserta eksternal.

Project ini terdiri dari **3 website**:

| No | Website | Fungsi |
|----|---------|--------|
| 1 | Website Utama | Informasi acara, pendaftaran peserta, time result |
| 2 | Website Panitia | Rekap peserta, start acara, scan barcode finis |
| 3 | Website Admin | Kelola konten, data peserta, user, dan konfigurasi |

Ketiga website menggunakan **satu backend API terpusat** berbasis
Laravel, dengan satu database MySQL.

---

## 2. TEKNOLOGI YANG DIGUNAKAN

| Komponen | Teknologi |
|----------|-----------|
| Framework Backend | Laravel 13 |
| Bahasa Pemrograman | PHP 8.3 |
| Database | MySQL 8.4 |
| Autentikasi | Laravel Sanctum (API Token) |
| Testing | PHPUnit (163 test) |
| Dokumentasi API | Markdown + Postman Collection |
| Version Control | Git |

---

## 3. STRUKTUR DATABASE

Backend menggunakan **12 tabel utama** + 1 tabel Sanctum:

| No | Tabel | Fungsi |
|----|-------|--------|
| 1 | categories | Kategori peserta (Siswa, Guru, Alumni, Orangtua, External) |
| 2 | packages | Paket pendaftaran (Gratis, 100K, 150K) |
| 3 | users | Akun admin & panitia |
| 4 | events | Data acara FunRun |
| 5 | participants | Data identitas peserta |
| 6 | registrations | Data pendaftaran peserta per event |
| 7 | event_categories | Relasi event dengan kategori |
| 8 | results | Hasil waktu tempuh peserta |
| 9 | scan_logs | Log semua percobaan scan barcode |
| 10 | contents | Konten website |
| 11 | settings | Konfigurasi global |
| 12 | activity_logs | Audit trail aksi admin/panitia |
| 13 | personal_access_tokens | Token autentikasi (Sanctum) |

### Relasi Utama

- 1 kategori punya banyak registrasi
- 1 paket punya banyak registrasi
- 1 peserta punya banyak registrasi (boleh daftar di banyak event)
- 1 registrasi punya 1 hasil waktu tempuh
- 1 user (panitia) bisa scan banyak hasil
- 1 user (admin) bisa mengubah banyak konten

---

## 4. DAFTAR ENDPOINT API

Backend memiliki **±50 endpoint API** yang terbagi dalam 4 kelompok:

### 4.1 Autentikasi (4 endpoint)

| Endpoint | Fungsi |
|----------|--------|
| POST /api/login | Login admin/panitia |
| POST /api/logout | Logout (hapus token) |
| GET /api/me | Cek profil sendiri |

### 4.2 Website Utama — Publik (9 endpoint)

| Endpoint | Fungsi |
|----------|--------|
| GET /api/event/active | Informasi acara aktif |
| GET /api/contents | Konten website utama |
| POST /api/registrations | Registrasi peserta |
| GET /api/results | Daftar waktu tempuh |
| GET /api/results/{no} | Waktu tempuh per nomor peserta |
| GET /api/categories | Daftar kategori |
| GET /api/packages | Daftar paket |
| POST /api/registrations/{no}/payment-proof | Upload bukti bayar |
| POST /api/registrations/check-status | Cek status pendaftaran |

### 4.3 Website Panitia (4 endpoint)

| Endpoint | Fungsi |
|----------|--------|
| GET /api/panitia/recap/category | Rekap peserta per kategori |
| GET /api/panitia/recap/participants | Rekap per nomor peserta |
| POST /api/panitia/start | Mulai acara (start serentak) |
| POST /api/panitia/scan | Scan barcode finis |

### 4.4 Website Admin (33 endpoint)

**Konten (5):** GET/POST/PUT/DELETE /api/admin/contents

**Data (5):** participants, registrations, verify, dashboard

**User (5):** GET/POST/PUT/DELETE /api/admin/users

**Event (6):** GET/POST/PUT/DELETE /api/admin/events + toggle-active + reset

**Scan Logs (2):** GET /api/admin/scan-logs, GET /api/admin/scan-logs/{id}

**Activity Logs (2):** GET /api/admin/activity-logs, GET /api/admin/activity-logs/{id}

**Settings (5):** GET/POST/PUT/DELETE /api/admin/settings

**Export (2):** GET /api/admin/export/participants, GET /api/admin/export/registrations

---

## 5. STATISTIK TESTING

Backend telah melalui **testing otomatis menyeluruh**:

| No | File Test | Jumlah Test |
|----|-----------|-------------|
| 1 | AuthTest | 6 |
| 2 | RegistrationTest | 8 |
| 3 | EventContentTest | 5 |
| 4 | ResultTest | 6 |
| 5 | PanitiaTest | 8 |
| 6 | AdminContentTest | 9 |
| 7 | AdminDataTest | 12 |
| 8 | AdminUserTest | 14 |
| 9 | RoleAccessTest | 14 |
| 10 | CategoryPackageTest | 5 |
| 11 | CheckStatusTest | 6 |
| 12 | PaymentProofTest | 5 |
| 13 | AdminEventTest | 10 |
| 14 | ActivityLogTest | 7 |
| 15 | AdminScanLogTest | 10 |
| 16 | AdminSettingTest | 10 |
| 17 | AdminExportTest | 7 |
| 18 | AdminActivityLogTest | 11 |
| 19 | AdminResetEventTest | 9 |
| | **TOTAL** | **163 test** |

**Status:** ✅ **Semua lolos (505 assertions).**

---

## 6. FITUR UNGGULAN

### 6.1 Penomoran Peserta Otomatis
Format: `[KODE_KATEGORI]-[URUTAN 4 DIGIT]`
Contoh: SW-0001, GK-0001, AL-0001, OT-0001, EX-0001.

### 6.2 Barcode Unik
Setiap peserta mendapat barcode unik untuk di-scan di garis finis.

### 6.3 Scan Barcode dengan Deteksi Duplikat
- Scan valid → 200 OK
- Scan duplikat → 409 Conflict
- Barcode tidak ada → 404 Not Found
- Semua percobaan scan tercatat di `scan_logs`

### 6.4 Start Serentak
Panitia menekan tombol "Start Acara" → semua peserta mulai dihitung waktunya.

### 6.5 Perhitungan Waktu Tempuh
`duration` = `finish_time` - `start_time` (dalam detik).

### 6.6 Auto `payment_status = free`
Paket gratis otomatis berstatus `free` (bukan `unpaid`).

### 6.7 Upload Bukti Bayar
Peserta paket berbayar bisa upload bukti transfer (JPG/PNG/PDF, maks 2 MB).

### 6.8 Cek Status Pendaftaran
Peserta bisa cek status dengan nomor peserta atau email.

### 6.9 Notifikasi Email Verifikasi
Peserta dapat email otomatis saat pendaftaran diverifikasi admin.

### 6.10 Audit Trail
Semua aksi admin/panitia tercatat di `activity_logs` (siapa, kapan, IP, user agent).

### 6.11 Export CSV
Admin bisa export data peserta & pendaftaran dalam format CSV (Excel-compatible).

### 6.12 Reset Acara dengan Konfirmasi
Reset acara butuh konfirmasi `"RESET"` + password admin.

---

## 7. ALUR PROSES BISNIS

### 7.1 Alur Pendaftaran Peserta
- Peserta buka Website Utama
- Lihat informasi acara (GET /api/event/active)
- Lihat kategori & paket (GET /api/categories, GET /api/packages)
- Isi form pendaftaran
- Kirim data (POST /api/registrations)
→ Sistem cek: apakah email sudah terdaftar di event sama?
→ Jika belum: buat peserta + registrasi + nomor otomatis + barcode
→ Jika paket gratis: status = confirmed & free
→ Jika paket berbayar: status = pending & unpaid
- Peserta terima email berisi nomor peserta & barcode
- Jika paket berbayar: peserta transfer, lalu upload bukti bayar
- Admin verifikasi pendaftaran
- Peserta terima email verifikasi


### 7.2 Alur Hari Acara
- Panitia login ke Website Panitia
- Panitia cek rekap peserta
- Peserta berkumpul di garis start
- Panitia tekan tombol "Start Acara" (POST /api/panitia/start)
→ Semua peserta mulai dihitung waktunya
- Peserta lari mengelilingi rute
- Peserta mencapai garis finis
- Panitia scan barcode peserta (POST /api/panitia/scan)
→ Sistem cari peserta
→ Cek apakah sudah pernah discan (duplikat)
→ Hitung waktu tempuh
→ Simpan hasil ke tabel results
- Waktu tempuh peserta muncul di Website Utama (GET /api/results)


### 7.3 Alur Admin
- Admin login ke Website Admin
- Admin kelola konten website
- Admin lihat data peserta & pendaftaran
- Admin verifikasi pendaftaran (confirmed/unpaid → paid)
- Admin kelola user (tambah/hapus panitia)
- Admin lihat scan logs & activity logs
- Admin export data untuk laporan
- Admin reset acara jika perlu (gladi bersih → acara asli)


---

## 8. CATATAN TEKNIS

### 8.1 Keamanan

| Fitur | Status |
|-------|--------|
| Autentikasi Token (Sanctum) | ✅ |
| Middleware Role (admin/panitia) | ✅ |
| Rate Limiting Login (5x/menit) | ✅ |
| CORS untuk frontend | ✅ |
| Password di-hash (bcrypt) | ✅ |
| Admin tidak bisa hapus diri sendiri | ✅ |
| Reset acara butuh password | ✅ |
| Soft delete untuk data penting | ✅ |

### 8.2 Struktur Role

| Role | Akses |
|------|-------|
| admin | Semua endpoint |
| panitia | Endpoint panitia + auth |
| publik (tanpa token) | Endpoint publik |

### 8.3 Email Development

Saat ini menggunakan driver `log` — email tercatat di `storage/logs/laravel.log`.
Production akan menggunakan SMTP.

---

## 9. STATUS AKHIR

| Aspek | Status |
|-------|--------|
| Database | ✅ Final (12 tabel) |
| Backend API | ✅ Selesai (±50 endpoint) |
| Automated Test | ✅ 163 test lolos |
| Dokumentasi | ✅ Markdown + Postman |
| Keamanan | ✅ Lengkap |
| Audit Trail | ✅ Aktif |
| **Kesiapan Deploy** | ⏸️ **Hold** (menunggu keputusan) |

---

## PENUTUP

Backend Centaurian FunRun telah selesai dikembangkan dan
teruji secara menyeluruh. Seluruh fitur berjalan sesuai
spesifikasi, dengan **163 test otomatis** yang semuanya lolos.

Dokumen ini disusun sebagai laporan resmi untuk keperluan
internal tim acara Centaurian FunRun.

---

**Dokumen ini dicetak dari `docs/LAPORAN.md`.**
**Untuk versi terbaru, cek repository project.**