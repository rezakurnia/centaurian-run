# ALUR BACKEND — CENTAURIAN FUN RUN

Dokumen ini menjelaskan **alur proses backend** dari setiap fitur,
dalam bentuk diagram teks (ASCII) yang mudah dipahami.

---

## DAFTAR ALUR

1. Alur Autentikasi (Login/Logout)
2. Alur Registrasi Peserta
3. Alur Upload Bukti Bayar
4. Alur Verifikasi Pendaftaran
5. Alur Start Acara
6. Alur Scan Barcode (Finis)
7. Alur Time Result
8. Alur Rekap Peserta
9. Alur CRUD Konten
10. Alur CRUD Event
11. Alur CRUD User
12. Alur Audit Trail (Activity Log)
13. Alur Reset Acara
14. Alur Export Data

---

## 1. ALUR AUTENTIKASI

```
┌─────────────────────┐
│  Frontend / Client  │
└──────────┬──────────┘
           │
           │ POST /api/login
           │ {username, password}
           ▼
┌─────────────────────────────┐
│  AuthController@login       │
│  1. Validasi input          │
│  2. Cari user by username   │
│  3. Cek password (Hash)     │
│  4. Update last_login       │
│  5. Buat token Sanctum      │
└──────────┬──────────────────┘
           │
           │ Response 200
           │ {message, user, token}
           ▼
┌─────────────────────┐
│  Frontend simpan    │
│  token di storage   │
└──────────┬──────────┘
           │
           │ Request berikutnya
           │ Authorization: Bearer <token>
           ▼
┌─────────────────────────────┐
│  Middleware auth:sanctum    │
│  + Middleware role:...      │
│  1. Cek token valid         │
│  2. Cek role user           │
│  3. Lanjut ke controller    │
└─────────────────────────────┘

LOGOUT:
Frontend → POST /api/logout
        → AuthController@logout
        → Hapus currentAccessToken
        → Response 200
```

---

## 2. ALUR REGISTRASI PESERTA

```
Peserta → POST /api/registrations
          {full_name, gender, birth_place, birth_date,
           motivation, email, phone, event_id,
           category_id, package_id}
          │
          ▼
┌──────────────────────────────────────┐
│  Validasi (StoreRegistrationRequest) │
│  - Semua field wajib valid           │
│  - event_id, category_id, package_id │
│    harus ada di database             │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Cek duplikat                        │
│  Apakah email ini sudah terdaftar    │
│  di event yang sama?                 │
└──────────┬───────────────────────────┘
           │
     ┌─────┴─────┐
     │           │
    Ya          Tidak
     │           │
     ▼           ▼
  Response    ┌────────────────────────────┐
  422         │  DB::transaction           │
  {email...}  │  1. Generate nomor peserta │
              │     (ParticipantNumber     │
              │      Service)              │
              │     → [KODE]-[URUTAN]      │
              │  2. firstOrCreate peserta  │
              │     (by email)             │
              │  3. Generate barcode       │
              │  4. Tentukan status paket: │
              │     - gratis → confirmed   │
              │     - bayar → pending      │
              │  5. Simpan registrasi      │
              └──────────┬─────────────────┘
                         │
                         ▼
              ┌──────────────────────────┐
              │  Kirim email konfirmasi  │
              │  (RegistrationMail)      │
              │  Update email_sent_at    │
              └──────────┬───────────────┘
                         │
                         ▼
              Response 201
              {participant, registration}
```

---

## 3. ALUR UPLOAD BUKTI BAYAR

```
Peserta → POST /api/registrations/{no}/payment-proof
          form-data: payment_proof (file)
          │
          ▼
┌──────────────────────────────────────┐
│  Validasi (UploadPaymentProofRequest)│
│  - File wajib: jpg/jpeg/png/pdf      │
│  - Maks 2 MB                         │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Cari registrasi by registration_    │
│  number                              │
└──────────┬───────────────────────────┘
           │
     ┌─────┴──────┐
     │            │
  Tidak Ada    Ada
     │            │
     ▼            ▼
  Response   ┌──────────────────────────┐
  404        │  Cek payment_status      │
             │  - free? → tolak 422     │
             │  - paid? → tolak 422     │
             │  - unpaid → lanjut       │
             └──────────┬───────────────┘
                        │
                        ▼
             ┌──────────────────────────┐
             │  Simpan file ke storage  │
             │  path: payment_proofs/   │
             │  Update payment_proof    │
             └──────────┬───────────────┘
                        │
                        ▼
             Response 200
             {registration dengan URL file}
```

---

## 4. ALUR VERIFIKASI PENDAFTARAN

```
Admin → PUT /api/admin/registrations/{id}/verify
        {registration_status, payment_status}
        Authorization: Bearer <token_admin>
        │
        ▼
┌──────────────────────────────────────┐
│  Middleware role:admin               │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Validasi (VerifyRegistrationRequest)│
│  - status: pending/confirmed/cancel  │
│  - payment: unpaid/paid/free         │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Cari registrasi                     │
│  Jika tidak ada → 404                │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Update registrasi:                  │
│  - registration_status               │
│  - payment_status                    │
│  - verified_by = admin id            │
│  - verified_at = now()               │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Kirim email verifikasi ke peserta   │
│  (RegistrationVerifiedMail)          │
│  - Nomor peserta & barcode           │
│  - Status baru                       │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Catat activity_log                  │
│  action: verify_registration         │
└──────────┬───────────────────────────┘
           │
           ▼
Response 200
```

---

## 5. ALUR START ACARA

```
Panitia → POST /api/panitia/start
          {event_id}
          Authorization: Bearer <token_panitia>
          │
          ▼
┌──────────────────────────────────────┐
│  Validasi event_id exists            │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  ScanService@startEvent              │
│  DB::transaction:                    │
│  1. Update event.start_time = now()  │
│  2. Update results yang belum punya  │
│     start_time                       │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Catat activity_log                  │
│  action: start_event                 │
└──────────┬───────────────────────────┘
           │
           ▼
Response 200
{event_id, start_time}

CATATAN:
- Start hanya bisa dilakukan sekali.
- Wajib dilakukan SEBELUM scan barcode.
```

---

## 6. ALUR SCAN BARCODE (FINIS)

```
Panitia → POST /api/panitia/scan
          {barcode}
          Authorization: Bearer <token_panitia>
          │
          ▼
┌──────────────────────────────────────┐
│  Validasi barcode                    │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Cari registrasi by barcode          │
└──────────┬───────────────────────────┘
           │
     ┌─────┴──────┐
     │            │
  Tidak Ada    Ada
     │            │
     ▼            ▼
  Catat        ┌────────────────────────┐
  scan_log     │  Cek event.start_time  │
  (invalid)    │  Jika belum → invalid  │
  Response     └──────────┬─────────────┘
  404                     │
                          ▼
                ┌────────────────────────┐
                │  Cek apakah sudah      │
                │  pernah discan?        │
                │  (Result sudah ada?)   │
                └──────────┬─────────────┘
                           │
                    ┌──────┴──────┐
                    │             │
                 Sudah         Belum
                    │             │
                    ▼             ▼
              Catat           ┌──────────────────┐
              scan_log        │  Hitung duration │
              (duplicate)     │  = finish_time   │
              Response        │    - start_time  │
              409             └────────┬─────────┘
                                       │
                                       ▼
                              ┌──────────────────┐
                              │  Simpan Result:  │
                              │  - registration  │
                              │  - participant   │
                              │  - event_id      │
                              │  - start_time    │
                              │  - finish_time   │
                              │  - duration      │
                              │  - scan_status=  │
                              │    valid         │
                              │  - scanned_by    │
                              └────────┬─────────┘
                                       │
                                       ▼
                              ┌──────────────────┐
                              │  Catat scan_log  │
                              │  (valid)         │
                              └────────┬─────────┘
                                       │
                                       ▼
                              Response 200
                              {no, nama, kategori,
                               start, finish, duration}
```

---

## 7. ALUR TIME RESULT

```
Publik → GET /api/results
         │
         ▼
┌──────────────────────────────────────┐
│  Query results                       │
│  - with participant, registration,   │
│    category                          │
│  - where scan_status = 'valid'       │
│  - order by duration ASC             │
│  - optional filter: category_id      │
└──────────┬───────────────────────────┘
           │
           ▼
Response 200
{
  data: [
    {
      registration_number,
      full_name,
      category,
      start_time,
      finish_time,
      duration,     ← dalam detik
      scan_status
    }
  ]
}

Per Nomor Peserta:
GET /api/results/{registration_number}
→ Cari by registration_number
→ Jika tidak ada → 404
→ Jika ada → tampilkan
```

---

## 8. ALUR REKAP PESERTA

```
Panitia → GET /api/panitia/recap/category
          │
          ▼
┌──────────────────────────────────────┐
│  Query: kategori LEFT JOIN           │
│         registrations                │
│  Group by kategori                   │
│  Count registrations.id              │
└──────────┬───────────────────────────┘
           │
           ▼
Response 200
{
  data: [
    {name: "Siswa", code: "SW", total_participants: 25},
    {name: "Guru", code: "GK", total_participants: 10},
    ...
  ]
}

Panitia → GET /api/panitia/recap/participants
          │
          ▼
┌──────────────────────────────────────┐
│  Query registrations                 │
│  - with participant, category,       │
│    package                           │
│  - order by registration_number ASC  │
└──────────┬───────────────────────────┘
           │
           ▼
Response 200
{data: [{no, nama, kategori, paket, status}]}
```

---

## 9. ALUR CRUD KONTEN

```
Admin → POST /api/admin/contents
        {target_site, section, title, body,
         status, sort_order}
        │
        ▼
┌──────────────────────────────────────┐
│  Validasi (StoreContentRequest)      │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Create content                      │
│  + updated_by = admin id             │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Catat activity_log                  │
│  action: create_content              │
└──────────┬───────────────────────────┘
           │
           ▼
Response 201

Update / Delete: alur mirip, dengan
action: update_content / delete_content
```

---

## 10. ALUR CRUD EVENT

```
Admin → POST /api/admin/events
        {name, description, location,
         event_date, start_time, is_active}
        │
        ▼
┌──────────────────────────────────────┐
│  Validasi (StoreEventRequest)        │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Create event                        │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Catat activity_log                  │
│  action: create_event                │
└──────────┬───────────────────────────┘
           │
           ▼
Response 201

DELETE /api/admin/events/{id}:
  → Cek apakah sudah ada registrations
  → Jika ada → tolak 422
  → Jika belum → hapus
  → Catat activity_log

PUT /api/admin/events/{id}/toggle-active:
  → Update is_active = !is_active
  → Catat activity_log
```

---

## 11. ALUR CRUD USER

```
Admin → POST /api/admin/users
        {username, password, email, role}
        │
        ▼
┌──────────────────────────────────────┐
│  Validasi (StoreUserRequest)         │
│  - username unik                     │
│  - password min 6                    │
│  - role: admin/panitia               │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Create user                         │
│  - Hash password                     │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  Catat activity_log                  │
│  action: create_user                 │
└──────────┬───────────────────────────┘
           │
           ▼
Response 201

DELETE /api/admin/users/{id}:
  → Cek: apakah hapus akun sendiri?
  → Jika ya → tolak 403
  → Jika bukan → soft delete
  → Catat activity_log
```

---

## 12. ALUR AUDIT TRAIL (ACTIVITY LOG)

```
Setiap aksi admin/panitia:
  → Trait LogsActivity::logActivity()
  → Simpan ke tabel activity_logs:
    - user_id
    - action
    - target_table
    - target_id
    - detail
    - ip_address
    - user_agent
    - created_at

Aksi yang dicatat:
  - create_content, update_content, delete_content
  - create_event, update_event, delete_event, toggle_event
  - create_user, update_user, delete_user
  - verify_registration
  - create_setting, update_setting, delete_setting
  - start_event
  - reset_event

Admin → GET /api/admin/activity-logs
        (dengan filter: user_id, action,
         target_table, date_from, date_to)
        │
        ▼
Response 200
{data: [...], meta: {current_page, total}}
```

---

## 13. ALUR RESET ACARA

```
Admin → POST /api/admin/events/{id}/reset
        {confirmation: "RESET", password: "..."}
        │
        ▼
┌──────────────────────────────────────┐
│  Validasi (ResetEventRequest)        │
│  - confirmation harus "RESET"        │
│  - password harus cocok dengan       │
│    password admin yang login         │
└──────────┬───────────────────────────┘
           │
     ┌─────┴──────┐
     │            │
  Gagal        Valid
     │            │
     ▼            ▼
  Response   ┌──────────────────────────┐
  422        │  DB::transaction:        │
             │  1. Hapus scan_logs      │
             │     terkait event        │
             │  2. Hapus results event  │
             │  3. Reset start_time     │
             │     event → null         │
             └──────────┬───────────────┘
                        │
                        ▼
             ┌──────────────────────────┐
             │  Catat activity_log      │
             │  action: reset_event     │
             └──────────┬───────────────┘
                        │
                        ▼
             Response 200
             {scan_logs_deleted,
              results_deleted,
              event_id}

PENTING:
- Data peserta & registrasi TETAP.
- Hanya data scan & start yang di-reset.
- Cocok untuk "gladi bersih → acara asli".
```

---

## 14. ALUR EXPORT DATA

```
Admin → GET /api/admin/export/participants
        │
        ▼
┌──────────────────────────────────────┐
│  Query participants                  │
│  - optional filter: category_id      │
└──────────┬───────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  streamDownload()                    │
│  - fputcsv() → tulis per baris       │
│  - Content-Type: text/csv            │
│  - File langsung di-stream           │
│    (tidak disimpan di server)        │
└──────────┬───────────────────────────┘
           │
           ▼
Response 200
File CSV (download otomatis)

Kolom CSV Peserta:
  ID, Nama, Gender, Tempat Lahir,
  Tanggal Lahir, Email, Phone, Motivasi,
  Email Terkirim, Terdaftar Pada

Kolom CSV Pendaftaran:
  No Peserta, Barcode, Nama, Email, Phone,
  Kategori, Paket, Event, Status Pendaftaran,
  Status Pembayaran, Terdaftar Pada
```

---

## RINGKASAN ALUR

| No | Alur | Trigger | Hasil |
|----|------|---------|-------|
| 1 | Autentikasi | Login form | Token Sanctum |
| 2 | Registrasi | Form daftar | Nomor + barcode |
| 3 | Upload Bukti | Form upload | File tersimpan |
| 4 | Verifikasi | Admin klik verify | Email terkirim |
| 5 | Start Acara | Panitia klik start | start_time tercatat |
| 6 | Scan Barcode | Panitia scan | duration dihitung |
| 7 | Time Result | Publik akses | Daftar waktu |
| 8 | Rekap | Panitia akses | Jumlah peserta |
| 9 | CRUD Konten | Admin kelola | Konten tersimpan |
| 10 | CRUD Event | Admin kelola | Event tersimpan |
| 11 | CRUD User | Admin kelola | User tersimpan |
| 12 | Activity Log | Setiap aksi | Tercatat otomatis |
| 13 | Reset Acara | Admin konfirmasi | Data scan direset |
| 14 | Export | Admin akses | File CSV |

---

**Dokumen ini adalah referensi alur backend Centaurian FunRun.**
**Setiap perubahan alur harus diperbarui di sini.**