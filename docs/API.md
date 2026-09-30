# API Documentation — Centaurian FunRun

Dokumentasi resmi API backend Centaurian FunRun.

- **Base URL (development):** `http://127.0.0.1:8000/api`
- **Format:** JSON
- **Autentikasi:** Bearer Token (Laravel Sanctum)
- **Header wajib:**
  - `Accept: application/json`
  - `Content-Type: application/json` (untuk request berbody)
  - `Authorization: Bearer <token>` (untuk endpoint terproteksi)

---

## Daftar Isi

1. [Autentikasi](#1-autentikasi)
2. [Website Utama — Publik](#2-website-utama--publik)
3. [Website Panitia](#3-website-panitia)
4. [Website Admin](#4-website-admin)
5. [Kode Status HTTP](#5-kode-status-http)
6. [Format Error Standar](#6-format-error-standar)
7. [Fitur Tambahan v1.1.0](#7-fitur-tambahan-v110)

---

## 1. Autentikasi

### 1.1 Login

**Endpoint:** `POST /login`

**Akses:** Publik

**Deskripsi:** Login untuk admin dan panitia. Mengembalikan token Sanctum.

**Request Body:**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| username | string | Ya | Username akun |
| password | string | Ya | Password akun |

**Contoh Request:**

```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Response Sukses (200):**

```json
{
  "message": "Login berhasil.",
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@centaurian.test",
    "role": "admin"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxx"
}
```

**Response Gagal — Kredensial Salah (422):**

```json
{
  "message": "Username atau password salah.",
  "errors": {
    "username": ["Username atau password salah."]
  }
}
```

**Response Gagal — Rate Limit (429):**

```json
{
  "message": "Terlalu banyak percobaan login. Coba lagi dalam 1 menit."
}
```

**Catatan:**
- Rate limit: **5 percobaan per menit** per kombinasi username + IP.
- Simpan `token` untuk request berikutnya.

---

### 1.2 Cek Profil Sendiri (`/me`)

**Endpoint:** `GET /me`

**Akses:** Butuh token

**Deskripsi:** Mengembalikan data user yang sedang login.

**Response Sukses (200):**

```json
{
  "id": 2,
  "username": "panitia",
  "email": "panitia@centaurian.test",
  "role": "panitia",
  "last_login": "2026-09-29T01:00:00.000000Z",
  "created_at": "2026-09-29T00:00:00.000000Z",
  "updated_at": "2026-09-29T01:00:00.000000Z",
  "deleted_at": null
}
```

**Response Gagal — Token Invalid (401):**

```json
{
  "message": "Unauthenticated."
}
```

---

### 1.3 Logout

**Endpoint:** `POST /logout`

**Akses:** Butuh token

**Deskripsi:** Menghapus token yang sedang dipakai.

**Response Sukses (200):**

```json
{
  "message": "Logout berhasil."
}
```

**Catatan:**
- Setelah logout, token tidak bisa dipakai lagi.
- Request berikutnya dengan token yang sama → **401 Unauthenticated**.

---

## 2. Website Utama — Publik

### 2.1 Informasi Acara Aktif

**Endpoint:** `GET /event/active`

**Akses:** Publik

**Deskripsi:** Mengembalikan data acara yang sedang aktif (`is_active = 1`).

**Response Sukses (200):**

```json
{
  "message": "Informasi acara aktif.",
  "data": {
    "id": 1,
    "name": "Centaurian FunRun 2026",
    "description": "Lari santai bersama keluarga besar Centaurian",
    "location": "Lapangan Sekolah Centaurian",
    "event_date": "2026-05-15",
    "start_time": null,
    "is_active": true
  }
}
```

**Response Gagal — Tidak Ada Acara Aktif (404):**

```json
{
  "message": "Belum ada acara aktif.",
  "data": null
}
```

---

### 2.2 Daftar Konten Website Utama

**Endpoint:** `GET /contents`

**Akses:** Publik

**Deskripsi:** Mengembalikan konten website utama yang sudah `published`, diurutkan berdasarkan `sort_order`.

**Response Sukses (200):**

```json
{
  "message": "Daftar konten website utama.",
  "data": [
    {
      "id": 1,
      "section": "banner",
      "title": "Selamat Datang",
      "body": "Ikuti Centaurian FunRun!",
      "sort_order": 1,
      "updated_at": "2026-09-29T01:00:00.000000Z"
    }
  ]
}
```

**Catatan:** Hanya konten dengan `status = published` dan `target_site = utama` yang muncul.

---

### 2.3 Registrasi Peserta

**Endpoint:** `POST /registrations`

**Akses:** Publik

**Deskripsi:** Mendaftarkan peserta baru. Sistem otomatis:
- Membuat nomor peserta (format `[KODE]-[URUTAN 4 DIGIT]`).
- Membuat barcode unik.
- Mengirim email konfirmasi (dengan driver `log` di development).

**Request Body:**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| full_name | string | Ya | Nama lengkap (maks 100) |
| gender | enum | Ya | `L` atau `P` |
| birth_place | string | Ya | Tempat lahir |
| birth_date | date | Ya | Format `YYYY-MM-DD` |
| motivation | text | Tidak | Kata-kata motivasi |
| email | email | Ya | Email (unik per event) |
| phone | string | Ya | No HP (maks 20) |
| event_id | int | Ya | ID event (harus ada) |
| category_id | int | Ya | ID kategori (harus ada) |
| package_id | int | Ya | ID paket (harus ada) |

**Contoh Request:**

```json
{
  "full_name": "Budi Santoso",
  "gender": "L",
  "birth_place": "Bandung",
  "birth_date": "2000-05-15",
  "motivation": "Tetap semangat!",
  "email": "budi@example.com",
  "phone": "08123456789",
  "event_id": 1,
  "category_id": 1,
  "package_id": 2
}
```

**Response Sukses (201):**

```json
{
  "message": "Pendaftaran berhasil. Nomor peserta & barcode telah dikirim ke email Anda.",
  "data": {
    "participant": {
      "id": 1,
      "full_name": "Budi Santoso",
      "gender": "L",
      "birth_place": "Bandung",
      "birth_date": "2000-05-15",
      "motivation": "Tetap semangat!",
      "email": "budi@example.com",
      "phone": "08123456789",
      "email_sent_at": "2026-09-29T01:00:00.000000Z",
      "created_at": "2026-09-29T01:00:00.000000Z"
    },
    "registration": {
      "id": 1,
      "registration_number": "SW-0001",
      "barcode": "A1B2C3D4-SW-0001",
      "registration_status": "pending",
      "payment_status": "unpaid",
      "category": "Siswa",
      "package": "Paket 100K",
      "event": "Centaurian FunRun 2026",
      "created_at": "2026-09-29T01:00:00.000000Z"
    }
  }
}
```

**Response Gagal — Validasi (422):**

```json
{
  "message": "The selected category id is invalid. (and 1 more error)",
  "errors": {
    "category_id": ["The selected category id is invalid."],
    "package_id": ["The selected package id is invalid."]
  }
}
```

**Response Gagal — Email Sudah Terdaftar di Event Sama (422):**

```json
{
  "message": "Email ini sudah terdaftar pada event tersebut.",
  "errors": {
    "email": ["Email ini sudah terdaftar pada event tersebut."]
  }
}
```

**Catatan:**
- Satu email **boleh daftar di beberapa event**, tapi **hanya 1x per event**.
- Nomor peserta & barcode **dikirim ke email** (cek `storage/logs/laravel.log` untuk driver log).

---

### 2.4 Time Result (Daftar)

**Endpoint:** `GET /results`

**Akses:** Publik

**Deskripsi:** Daftar waktu tempuh peserta, diurutkan dari yang **tercepat**.

**Query Parameter (opsional):**

| Parameter | Tipe | Keterangan |
|---|---|---|
| category_id | int | Filter berdasarkan kategori |

**Contoh:** `GET /results?category_id=1`

**Response Sukses (200):**

```json
{
  "message": "Daftar waktu tempuh peserta.",
  "data": [
    {
      "id": 1,
      "registration_number": "SW-0001",
      "full_name": "Budi Santoso",
      "category": "Siswa",
      "start_time": "2026-09-29T01:00:45.000000Z",
      "finish_time": "2026-09-29T01:04:25.468730Z",
      "duration": 220,
      "scan_status": "valid"
    }
  ]
}
```

**Catatan:** Hanya `scan_status = valid` yang ditampilkan. `duration` dalam **detik**.

---

### 2.5 Time Result per Nomor Peserta

**Endpoint:** `GET /results/{registration_number}`

**Akses:** Publik

**Contoh:** `GET /results/SW-0001`

**Response Sukses (200):**

```json
{
  "message": "Hasil waktu tempuh peserta.",
  "data": {
    "id": 1,
    "registration_number": "SW-0001",
    "full_name": "Budi Santoso",
    "category": "Siswa",
    "start_time": "2026-09-29T01:00:45.000000Z",
    "finish_time": "2026-09-29T01:04:25.468730Z",
    "duration": 220,
    "scan_status": "valid"
  }
}
```

**Response Gagal — Tidak Ditemukan (404):**

```json
{
  "message": "Hasil tidak ditemukan untuk nomor peserta tersebut.",
  "data": null
}
```

---

## 3. Website Panitia

Semua endpoint di bagian ini **butuh token** dengan `role = panitia` **atau** `admin`.

### 3.1 Rekap Peserta per Kategori

**Endpoint:** `GET /panitia/recap/category`

**Akses:** Panitia / Admin

**Deskripsi:** Menampilkan jumlah peserta per kategori.

**Response Sukses (200):**

```json
{
  "message": "Rekap jumlah peserta per kategori.",
  "data": [
    { "id": 1, "name": "Siswa", "code": "SW", "total_participants": 2 },
    { "id": 2, "name": "Guru Karyawan", "code": "GK", "total_participants": 1 },
    { "id": 3, "name": "Alumni", "code": "AL", "total_participants": 0 },
    { "id": 4, "name": "Orangtua", "code": "OT", "total_participants": 0 },
    { "id": 5, "name": "External", "code": "EX", "total_participants": 0 }
  ]
}
```

---

### 3.2 Rekap Peserta per Nomor Peserta

**Endpoint:** `GET /panitia/recap/participants`

**Akses:** Panitia / Admin

**Deskripsi:** Daftar peserta diurutkan berdasarkan nomor peserta.

**Response Sukses (200):**

```json
{
  "message": "Rekap peserta berdasarkan nomor peserta.",
  "data": [
    {
      "registration_number": "SW-0001",
      "full_name": "Budi Santoso",
      "category": "Siswa",
      "package": "Paket 100K",
      "registration_status": "pending",
      "payment_status": "unpaid"
    }
  ]
}
```

---

### 3.3 Start Acara (Tombol Start Serentak)

**Endpoint:** `POST /panitia/start`

**Akses:** Panitia / Admin

**Deskripsi:** Menandai acara telah dimulai. Mengisi `start_time` di event & semua `results` yang belum punya `start_time`.

**Request Body:**

| Field | Tipe | Wajib |
|---|---|---|
| event_id | int | Ya |

**Contoh Request:**

```json
{
  "event_id": 1
}
```

**Response Sukses (200):**

```json
{
  "message": "Acara telah dimulai. Waktu start tercatat.",
  "data": {
    "event_id": 1,
    "start_time": "2026-09-29T01:00:45.000000Z"
  }
}
```

**Catatan:** Start **wajib** dijalankan sebelum scan.

---

### 3.4 Scan Barcode Peserta

**Endpoint:** `POST /panitia/scan`

**Akses:** Panitia / Admin

**Deskripsi:** Scan barcode peserta di garis finis. Menghitung `duration` = `finish_time` − `start_time`.

**Request Body:**

| Field | Tipe | Wajib |
|---|---|---|
| barcode | string | Ya |

**Contoh Request:**

```json
{
  "barcode": "A1B2C3D4-SW-0001"
}
```

**Response Sukses (200):**

```json
{
  "message": "Scan berhasil.",
  "data": {
    "registration_number": "SW-0001",
    "full_name": "Budi Santoso",
    "category": "Siswa",
    "start_time": "2026-09-29T01:00:45.000000Z",
    "finish_time": "2026-09-29T01:04:25.468730Z",
    "duration": 220
  }
}
```

**Response Gagal — Duplikat (409):**

```json
{
  "message": "Peserta sudah pernah discan.",
  "data": {
    "registration_number": "SW-0001",
    "full_name": "Budi Santoso",
    "finish_time": "2026-09-29T01:04:25.468730Z",
    "duration": 220
  }
}
```

**Response Gagal — Barcode Tidak Ditemukan (404):**

```json
{
  "message": "Barcode tidak ditemukan.",
  "data": null
}
```

**Response Gagal — Acara Belum Dimulai (404):**

```json
{
  "message": "Acara belum dimulai.",
  "data": null
}
```

**Catatan:**
- Setiap percobaan scan (valid/invalid/duplikat) dicatat di `scan_logs`.
- `duration` dalam detik.

---

## 4. Website Admin

Semua endpoint di bagian ini **butuh token** dengan `role = admin`.

### 4.1 CRUD Konten

#### 4.1.1 Daftar Konten

**Endpoint:** `GET /admin/contents`

**Akses:** Admin

**Deskripsi:** Daftar semua konten (draft + published).

**Response Sukses (200):**

```json
{
  "message": "Daftar semua konten.",
  "data": [
    {
      "id": 1,
      "section": "banner",
      "title": "Selamat Datang",
      "body": "Ikuti Centaurian FunRun!",
      "sort_order": 1,
      "updated_at": "2026-09-29T01:00:00.000000Z"
    }
  ]
}
```

---

#### 4.1.2 Buat Konten

**Endpoint:** `POST /admin/contents`

**Akses:** Admin

**Request Body:**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| target_site | enum | Ya | `utama` atau `panitia` |
| section | string | Tidak | Bagian konten |
| title | string | Tidak | Judul |
| body | text | Tidak | Isi |
| status | enum | Ya | `draft` atau `published` |
| sort_order | int | Tidak | Urutan tampil |

**Contoh Request:**

```json
{
  "target_site": "utama",
  "section": "banner",
  "title": "Selamat Datang",
  "body": "Ikuti Centaurian FunRun!",
  "status": "published",
  "sort_order": 1
}
```

**Response Sukses (201):**

```json
{
  "message": "Konten berhasil dibuat.",
  "data": {
    "id": 1,
    "section": "banner",
    "title": "Selamat Datang",
    "body": "Ikuti Centaurian FunRun!",
    "sort_order": 1,
    "updated_at": "2026-09-29T01:00:00.000000Z"
  }
}
```

---

#### 4.1.3 Detail Konten

**Endpoint:** `GET /admin/contents/{id}`

**Akses:** Admin

**Response Sukses (200):** Sama seperti di atas.

**Response Gagal (404):**

```json
{ "message": "Konten tidak ditemukan.", "data": null }
```

---

#### 4.1.4 Update Konten

**Endpoint:** `PUT /admin/contents/{id}`

**Akses:** Admin

**Request Body:** Sama seperti buat konten, semua field **opsional** (`sometimes`).

**Response Sukses (200):**

```json
{
  "message": "Konten berhasil diperbarui.",
  "data": { ... }
}
```

---

#### 4.1.5 Hapus Konten

**Endpoint:** `DELETE /admin/contents/{id}`

**Akses:** Admin

**Response Sukses (200):**

```json
{ "message": "Konten berhasil dihapus.", "data": null }
```

---

### 4.2 Collect Data

#### 4.2.1 Daftar Peserta

**Endpoint:** `GET /admin/participants`

**Akses:** Admin

**Query Parameter (opsional):**

| Parameter | Tipe | Keterangan |
|---|---|---|
| category_id | int | Filter kategori |
| search | string | Cari nama / email / no peserta |

**Response Sukses (200):**

```json
{
  "message": "Daftar peserta.",
  "data": [
    {
      "id": 1,
      "full_name": "Budi Santoso",
      "gender": "L",
      "email": "budi@example.com",
      "phone": "08123456789",
      ...
    }
  ]
}
```

---

#### 4.2.2 Daftar Pendaftaran

**Endpoint:** `GET /admin/registrations`

**Akses:** Admin

**Query Parameter (opsional):**

| Parameter | Tipe |
|---|---|
| category_id | int |
| registration_status | enum |
| payment_status | enum |

**Response Sukses (200):**

```json
{
  "message": "Daftar pendaftaran.",
  "data": [
    {
      "id": 1,
      "registration_number": "SW-0001",
      "barcode": "A1B2C3D4-SW-0001",
      "registration_status": "pending",
      "payment_status": "unpaid",
      "category": "Siswa",
      "package": "Paket 100K",
      "event": "Centaurian FunRun 2026",
      "created_at": "2026-09-29T01:00:00.000000Z"
    }
  ]
}
```

---

#### 4.2.3 Detail Pendaftaran

**Endpoint:** `GET /admin/registrations/{id}`

**Akses:** Admin

**Response Sukses (200):** Sama seperti item di atas.

**Response Gagal (404):**

```json
{ "message": "Pendaftaran tidak ditemukan.", "data": null }
```

---

#### 4.2.4 Verifikasi Pendaftaran

**Endpoint:** `PUT /admin/registrations/{id}/verify`

**Akses:** Admin

**Request Body:**

| Field | Tipe | Wajib |
|---|---|---|
| registration_status | enum | Ya (`pending`/`confirmed`/`cancelled`) |
| payment_status | enum | Ya (`unpaid`/`paid`/`free`) |

**Contoh Request:**

```json
{
  "registration_status": "confirmed",
  "payment_status": "paid"
}
```

**Response Sukses (200):**

```json
{
  "message": "Pendaftaran berhasil diverifikasi.",
  "data": { ... }
}
```

---

#### 4.2.5 Dashboard

**Endpoint:** `GET /admin/dashboard`

**Akses:** Admin

**Deskripsi:** Ringkasan angka untuk halaman utama admin.

**Response Sukses (200):**

```json
{
  "message": "Ringkasan data untuk dashboard admin.",
  "data": {
    "total_participants": 3,
    "total_registrations": 3,
    "by_category": [
      { "id": 1, "name": "Siswa", "code": "SW", "total": 2 }
    ],
    "by_payment_status": [
      { "payment_status": "paid", "total": 1 },
      { "payment_status": "unpaid", "total": 2 }
    ],
    "by_registration_status": [
      { "registration_status": "confirmed", "total": 1 },
      { "registration_status": "pending", "total": 2 }
    ]
  }
}
```

---

### 4.3 Kelola User

#### 4.3.1 Daftar User

**Endpoint:** `GET /admin/users`

**Akses:** Admin

**Response Sukses (200):**

```json
{
  "message": "Daftar user.",
  "data": [
    { "id": 1, "username": "admin", "email": "admin@centaurian.test", "role": "admin", "last_login": null, "created_at": "..." },
    { "id": 2, "username": "panitia", "email": "panitia@centaurian.test", "role": "panitia", "last_login": null, "created_at": "..." }
  ]
}
```

**Catatan:** Password **tidak** ditampilkan.

---

#### 4.3.2 Buat User

**Endpoint:** `POST /admin/users`

**Akses:** Admin

**Request Body:**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| username | string | Ya | Unik, maks 50 |
| password | string | Ya | Minimal 6 karakter |
| email | email | Tidak | |
| role | enum | Ya | `admin` atau `panitia` |

**Contoh Request:**

```json
{
  "username": "panitia2",
  "password": "rahasia123",
  "email": "panitia2@centaurian.test",
  "role": "panitia"
}
```

**Response Sukses (201):**

```json
{
  "message": "User berhasil dibuat.",
  "data": {
    "id": 3,
    "username": "panitia2",
    "email": "panitia2@centaurian.test",
    "role": "panitia",
    "last_login": null,
    "created_at": "2026-09-29T01:00:00.000000Z"
  }
}
```

---

#### 4.3.3 Detail User

**Endpoint:** `GET /admin/users/{id}`

**Akses:** Admin

**Response Sukses (200):** Sama seperti item di atas.

---

#### 4.3.4 Update User

**Endpoint:** `PUT /admin/users/{id}`

**Akses:** Admin

**Request Body:** Semua field **opsional**.

**Response Sukses (200):**

```json
{ "message": "User berhasil diperbarui.", "data": { ... } }
```

---

#### 4.3.5 Hapus User

**Endpoint:** `DELETE /admin/users/{id}`

**Akses:** Admin

**Response Sukses (200):**

```json
{ "message": "User berhasil dihapus.", "data": null }
```

**Response Gagal — Hapus Diri Sendiri (403):**

```json
{ "message": "Tidak dapat menghapus akun sendiri.", "data": null }
```

**Catatan:** Soft delete — data tidak benar-benar dihapus (`deleted_at` terisi).

---

## 5. Kode Status HTTP

| Kode | Arti |
|---|---|
| 200 | Sukses |
| 201 | Berhasil dibuat |
| 401 | Tidak terautentikasi (token invalid / expired) |
| 403 | Tidak punya akses (role salah / aksi ditolak) |
| 404 | Data tidak ditemukan |
| 409 | Konflik (duplikat, mis. scan ganda) |
| 422 | Validasi gagal |
| 429 | Terlalu banyak request (rate limit) |
| 500 | Kesalahan server |

---

## 6. Format Error Standar

### 6.1 Validasi Gagal (422)

```json
{
  "message": "Pesan error utama.",
  "errors": {
    "field_name": ["Pesan error untuk field ini."]
  }
}
```

### 6.2 Tidak Terautentikasi (401)

```json
{
  "message": "Unauthenticated."
}
```

### 6.3 Role Tidak Sesuai (403)

```json
{
  "message": "Unauthorized. Role tidak sesuai."
}
```

### 6.4 Tidak Ditemukan (404)

```json
{
  "message": "Pesan error spesifik.",
  "data": null
}
```
---

# 7. Fitur Tambahan v1.1.0

Fitur-fitur tambahan yang ditambahkan setelah versi awal.

---

## 7.1 Endpoint Publik — Kategori & Paket

### 7.1.1 Daftar Kategori

**Endpoint:** `GET /categories`

**Akses:** Publik

**Deskripsi:** Daftar semua kategori peserta.

**Response Sukses (200):**

```json
{
  "message": "Daftar kategori peserta.",
  "data": [
    { "id": 1, "name": "Siswa", "code": "SW" },
    { "id": 2, "name": "Guru Karyawan", "code": "GK" },
    { "id": 3, "name": "Alumni", "code": "AL" },
    { "id": 4, "name": "Orangtua", "code": "OT" },
    { "id": 5, "name": "External", "code": "EX" }
  ]
}
```

---

### 7.1.2 Daftar Paket

**Endpoint:** `GET /packages`

**Akses:** Publik

**Deskripsi:** Daftar semua paket pendaftaran, diurutkan dari harga terendah.

**Response Sukses (200):**

```json
{
  "message": "Daftar paket pendaftaran.",
  "data": [
    { "id": 1, "name": "Gratis",     "price": 0,      "description": "Siswa tertentu, Guru & Karyawan" },
    { "id": 2, "name": "Paket 100K", "price": 100000, "description": "Siswa" },
    { "id": 3, "name": "Paket 150K", "price": 150000, "description": "Orangtua, Alumni & External" }
  ]
}
```

---

## 7.2 Auto `payment_status = free`

**Berlaku otomatis** saat peserta mendaftar dengan **paket gratis** (`price = 0`).

**Perilaku:**
- `payment_status` → `free` (bukan `unpaid`).
- `registration_status` → `confirmed` (langsung aktif, tanpa verifikasi).

**Tidak ada endpoint baru** — ini perilaku otomatis pada `POST /registrations`.

---

## 7.3 Upload Bukti Bayar

**Endpoint:** `POST /registrations/{registration_number}/payment-proof`

**Akses:** Publik (peserta yang sudah daftar).

**Deskripsi:** Upload bukti transfer untuk paket berbayar.

**Request:** `multipart/form-data`

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| payment_proof | file | Ya | JPG, JPEG, PNG, atau PDF. Maks 2 MB. |

**Response Sukses (200):**

```json
{
  "message": "Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.",
  "data": {
    "id": 1,
    "registration_number": "SW-0001",
    "payment_status": "unpaid",
    "payment_proof": "payment_proofs/xxxxx.jpg",
    "payment_proof_url": "http://127.0.0.1:8000/storage/payment_proofs/xxxxx.jpg"
  }
}
```

**Response Gagal — Format File Salah (422):**
```json
{
  "message": "File harus berupa JPG, JPEG, PNG, atau PDF.",
  "errors": { "payment_proof": ["..."] }
}
```

**Response Gagal — Paket Gratis (422):**
```json
{
  "message": "Paket gratis tidak memerlukan bukti pembayaran.",
  "data": null
}
```

---

## 7.4 Cek Status Pendaftaran

**Endpoint:** `POST /registrations/check-status`

**Akses:** Publik

**Deskripsi:** Cek status pendaftaran dengan nomor peserta **atau** email.

**Request Body:**

| Field | Tipe | Wajib |
|---|---|---|
| registration_number | string | Salah satu |
| email | email | Salah satu |

**Contoh Request:**
```json
{
  "registration_number": "SW-0001"
}
```

**Response Sukses (200):**
```json
{
  "message": "Status pendaftaran ditemukan.",
  "data": [
    {
      "id": 1,
      "registration_number": "SW-0001",
      "registration_status": "confirmed",
      "payment_status": "paid",
      "category": "Siswa",
      "package": "Paket 100K",
      "event": "Centaurian FunRun 2026"
    }
  ]
}
```

**Response Gagal — Tidak Ditemukan (404):**
```json
{
  "message": "Pendaftaran tidak ditemukan.",
  "data": []
}
```

**Response Gagal — Tidak Isi Field (422):**
```json
{
  "message": "...",
  "errors": { "identifier": ["Isi minimal salah satu: registration_number atau email."] }
}
```

---

## 7.5 CRUD Event oleh Admin

**Akses:** Admin

### 7.5.1 Daftar Event
**Endpoint:** `GET /admin/events`

**Response (200):** Daftar semua event.

### 7.5.2 Buat Event
**Endpoint:** `POST /admin/events`

**Request Body:**
```json
{
  "name": "Centaurian FunRun 2026",
  "description": "Lari santai",
  "location": "Lapangan Sekolah",
  "event_date": "2026-05-15",
  "is_active": true
}
```

### 7.5.3 Detail Event
**Endpoint:** `GET /admin/events/{id}`

### 7.5.4 Update Event
**Endpoint:** `PUT /admin/events/{id}`

### 7.5.5 Hapus Event
**Endpoint:** `DELETE /admin/events/{id}`

**Catatan:** Event **tidak dapat dihapus** jika sudah ada peserta terdaftar → **422**.

### 7.5.6 Toggle Aktif/Non-Aktif
**Endpoint:** `PUT /admin/events/{id}/toggle-active`

**Response (200):**
```json
{
  "message": "Status event berhasil diubah.",
  "data": { "id": 1, "is_active": false }
}
```

---

## 7.6 Notifikasi Email Verifikasi

**Tidak ada endpoint baru** — email dikirim otomatis saat admin memverifikasi pendaftaran (`PUT /admin/registrations/{id}/verify`).

**Isi email:**
- Nama peserta.
- Nomor peserta & barcode.
- Kategori, paket, event.
- Status pendaftaran & pembayaran.

**Catatan:** Di development (`MAIL_MAILER=log`), email tercatat di `storage/logs/laravel.log`.

---

## 7.7 Activity Log Otomatis

**Berlaku otomatis** pada aksi admin & panitia:
- CRUD konten → `create_content`, `update_content`, `delete_content`
- CRUD user → `create_user`, `update_user`, `delete_user`
- CRUD event → `create_event`, `update_event`, `delete_event`, `toggle_event`
- Verifikasi pendaftaran → `verify_registration`
- CRUD setting → `create_setting`, `update_setting`, `delete_setting`
- Start acara → `start_event`
- Reset acara → `reset_event`

**Field tercatat:** `user_id`, `action`, `target_table`, `target_id`, `detail`, `ip_address`, `user_agent`, `created_at`.

**Tidak ada endpoint** — tercatat otomatis.

---

## 7.8 Admin Lihat Scan Logs

**Akses:** Admin

### 7.8.1 Daftar Scan Logs
**Endpoint:** `GET /admin/scan-logs`

**Query Parameter (opsional):**

| Parameter | Tipe | Keterangan |
|---|---|---|
| scan_status | enum | `valid`, `invalid`, `duplicate` |
| barcode | string | Filter partial match |
| date_from | date | Format `YYYY-MM-DD` |
| date_to | date | Format `YYYY-MM-DD` |

**Response (200):** Daftar scan log dengan pagination (50 per halaman).

### 7.8.2 Detail Scan Log
**Endpoint:** `GET /admin/scan-logs/{id}`

**Response (200):** Detail scan log dengan data peserta & panitia yang scan.

---

## 7.9 CRUD Settings

**Akses:** Admin

### 7.9.1 Daftar Settings
**Endpoint:** `GET /admin/settings`

### 7.9.2 Buat Setting
**Endpoint:** `POST /admin/settings`

**Request Body:**
```json
{
  "key": "app_name",
  "value": "Centaurian FunRun 2026"
}
```

### 7.9.3 Detail Setting
**Endpoint:** `GET /admin/settings/{key}`

### 7.9.4 Update Setting
**Endpoint:** `PUT /admin/settings/{key}`

**Request Body:**
```json
{
  "value": "Nilai Baru"
}
```

### 7.9.5 Hapus Setting
**Endpoint:** `DELETE /admin/settings/{key}`

---

## 7.10 Export Data (CSV)

**Akses:** Admin

### 7.10.1 Export Peserta
**Endpoint:** `GET /admin/export/participants`

**Query Parameter (opsional):** `category_id`

**Response:** File CSV (download otomatis).

**Kolom CSV:** ID, Nama, Gender, Tempat Lahir, Tanggal Lahir, Email, Phone, Motivasi, Email Terkirim, Terdaftar Pada.

### 7.10.2 Export Pendaftaran
**Endpoint:** `GET /admin/export/registrations`

**Query Parameter (opsional):** `category_id`, `event_id`, `registration_status`, `payment_status`

**Kolom CSV:** No Peserta, Barcode, Nama, Email, Phone, Kategori, Paket, Event, Status Pendaftaran, Status Pembayaran, Terdaftar Pada.

---

## 7.11 Admin Lihat Activity Logs

**Akses:** Admin

### 7.11.1 Daftar Activity Logs
**Endpoint:** `GET /admin/activity-logs`

**Query Parameter (opsional):**

| Parameter | Tipe |
|---|---|
| user_id | int |
| action | string |
| target_table | string |
| date_from | date |
| date_to | date |

**Response (200):** Daftar activity log dengan pagination (50 per halaman).

### 7.11.2 Detail Activity Log
**Endpoint:** `GET /admin/activity-logs/{id}`

---

## 7.12 Reset Acara

**Endpoint:** `POST /admin/events/{id}/reset`

**Akses:** Admin

**Deskripsi:** Reset acara — hapus `start_time`, `results`, dan `scan_logs` terkait. **Data peserta & registrasi TETAP.**

**Keamanan:**
- Wajib kirim `confirmation: "RESET"` (huruf kapital).
- Wajib kirim password admin yang sedang login.

**Request Body:**
```json
{
  "confirmation": "RESET",
  "password": "admin123"
}
```

**Response Sukses (200):**
```json
{
  "message": "Acara berhasil direset. Data scan & hasil telah dihapus.",
  "data": {
    "scan_logs_deleted": 5,
    "results_deleted": 3,
    "event_id": 1
  }
}
```

**Response Gagal — Konfirmasi Salah (422):**
```json
{
  "message": "...",
  "errors": { "confirmation": ["Ketik \"RESET\" (huruf kapital) untuk konfirmasi."] }
}
```

**Response Gagal — Password Salah (422):**
```json
{
  "message": "...",
  "errors": { "password": ["Password admin salah."] }
}
```

---

**Akhir dokumentasi v1.1.0.**
---

**Dokumentasi ini adalah sumber kebenaran kontrak API. Setiap perubahan endpoint harus diperbarui di sini.**