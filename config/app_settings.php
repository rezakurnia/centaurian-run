<?php

/**
 * Definisi setting global aplikasi Centaurian Run.
 *
 * Setiap setting punya:
 * - key       : identifier unik (dipakai di database & API)
 * - label     : nama tampilan untuk admin
 * - type      : tipe input (text, email, url, number, textarea, toggle, date, time)
 * - default   : nilai default jika belum diisi
 * - group     : grup untuk grouping di UI
 * - options   : (opsional) pilihan untuk tipe select
 */
return [
    // ========================================================================
    // GRUP 1: IDENTITAS ACARA
    // ========================================================================
    [
        'key' => 'app_name',
        'label' => 'Nama Aplikasi',
        'type' => 'text',
        'default' => 'Centaurian Run',
        'group' => 'Identitas Acara',
    ],
    [
        'key' => 'event_name',
        'label' => 'Nama Event',
        'type' => 'text',
        'default' => 'Centaurian Run 2026',
        'group' => 'Identitas Acara',
    ],
    [
        'key' => 'event_tagline',
        'label' => 'Tagline',
        'type' => 'text',
        'default' => 'MOVE WITH FAITH, RUN WITH CARE',
        'group' => 'Identitas Acara',
    ],
    [
        'key' => 'event_year',
        'label' => 'Tahun Acara',
        'type' => 'number',
        'default' => '2026',
        'group' => 'Identitas Acara',
    ],
    [
        'key' => 'event_description',
        'label' => 'Deskripsi Singkat',
        'type' => 'textarea',
        'default' => '',
        'group' => 'Identitas Acara',
    ],

    // ========================================================================
    // GRUP 2: KONTAK & SOSIAL MEDIA
    // ========================================================================
    [
        'key' => 'contact_email',
        'label' => 'Email Kontak',
        'type' => 'email',
        'default' => 'info.centaurian@alfacentauri.sch.id',
        'group' => 'Kontak & Sosial Media',
    ],
    [
        'key' => 'contact_phone',
        'label' => 'No. HP Panitia',
        'type' => 'text',
        'default' => '0821-1094-2273',
        'group' => 'Kontak & Sosial Media',
    ],
    [
        'key' => 'whatsapp_url',
        'label' => 'Link WhatsApp',
        'type' => 'url',
        'default' => '',
        'group' => 'Kontak & Sosial Media',
    ],
    [
        'key' => 'instagram_url',
        'label' => 'URL Instagram',
        'type' => 'url',
        'default' => 'https://www.instagram.com/centaurian_run',
        'group' => 'Kontak & Sosial Media',
    ],
    [
        'key' => 'facebook_url',
        'label' => 'URL Facebook',
        'type' => 'url',
        'default' => '',
        'group' => 'Kontak & Sosial Media',
    ],

    // ========================================================================
    // GRUP 3: KONFIGURASI PENDAFTARAN
    // ========================================================================
    [
        'key' => 'registration_open',
        'label' => 'Pendaftaran Dibuka',
        'type' => 'toggle',
        'default' => '1',
        'group' => 'Konfigurasi Pendaftaran',
    ],
    [
        'key' => 'max_participants',
        'label' => 'Maks Peserta',
        'type' => 'number',
        'default' => '500',
        'group' => 'Konfigurasi Pendaftaran',
    ],
    [
        'key' => 'registration_deadline',
        'label' => 'Batas Akhir Pendaftaran',
        'type' => 'date',
        'default' => '',
        'group' => 'Konfigurasi Pendaftaran',
    ],
    [
        'key' => 'auto_confirm_payment',
        'label' => 'Auto Konfirmasi Paket Gratis',
        'type' => 'toggle',
        'default' => '1',
        'group' => 'Konfigurasi Pendaftaran',
    ],

    // ========================================================================
    // GRUP 4: KONFIGURASI SCAN
    // ========================================================================
    [
        'key' => 'scan_cooldown_seconds',
        'label' => 'Jeda Antar Scan (detik)',
        'type' => 'number',
        'default' => '3',
        'group' => 'Konfigurasi Scan',
    ],
    [
        'key' => 'scan_sound_enabled',
        'label' => 'Suara Saat Scan',
        'type' => 'toggle',
        'default' => '1',
        'group' => 'Konfigurasi Scan',
    ],
    [
        'key' => 'scan_auto_clear_seconds',
        'label' => 'Auto Clear Hasil Scan (detik)',
        'type' => 'number',
        'default' => '5',
        'group' => 'Konfigurasi Scan',
    ],
    [
        'key' => 'event_start_hour',
        'label' => 'Jam Start Acara',
        'type' => 'text',
        'default' => '06:00',
        'group' => 'Konfigurasi Scan',
    ],
    [
        'key' => 'finish_button_visible_minutes',
        'label' => 'Tombol Akhiri Muncul Setelah (menit)',
        'type' => 'number',
        'default' => '60',
        'group' => 'Konfigurasi Scan',
    ],

    // ========================================================================
    // GRUP 5: TAMPILAN & BRANDING
    // ========================================================================
    [
        'key' => 'primary_color',
        'label' => 'Warna Utama',
        'type' => 'text',
        'default' => '#2563EB',
        'group' => 'Tampilan & Branding',
    ],
    [
        'key' => 'primary_light',
        'label' => 'Warna Utama (Terang)',
        'type' => 'text',
        'default' => '#3B82F6',
        'group' => 'Tampilan & Branding',
    ],
    [
        'key' => 'secondary_color',
        'label' => 'Warna Sekunder (Lembut)',
        'type' => 'text',
        'default' => '#EFF6FF',
        'group' => 'Tampilan & Branding',
    ],
    [
        'key' => 'accent_color',
        'label' => 'Warna Aksen',
        'type' => 'text',
        'default' => '#1E40AF',
        'group' => 'Tampilan & Branding',
    ],

    // ========================================================================
    // GRUP 6: TATA TERTIB
    // Format teks: baris "# Judul bagian" membuka bagian baru, baris lain jadi
    // satu butir "Label: isi" (label boleh dikosongkan). Lihat TataTertibService.
    // ========================================================================
    [
        'key' => 'tata_tertib',
        'label' => 'Tata Tertib Acara',
        'type' => 'textarea',
        'default' => <<<'TEXT'
# 1. Pendaftaran & Kepesertaan (Move with Faith)
Nomor Dada (BIB): Peserta wajib memasang nomor dada (BIB) di bagian depan baju selama acara berlangsung untuk memudahkan identifikasi dan dokumentasi.
Kepemilikan BIB: BIB tidak dapat dipindahtangankan demi alasan keselamatan medis dan akurasi data kepesertaan.
Komitmen Kebersamaan: Seluruh elemen ekosistem YTCK diharapkan hadir dan berpartisipasi aktif sebagai bentuk kesatuan gerak yang solid dan harmonis.

# 2. Etika & Solidaritas (Run with Care)
Aksi Nyata Kemanusiaan: Mengingat acara ini mengusung misi solidaritas Palestina, peserta diimbau untuk menjaga ketertiban, kedamaian, dan adab selama menyampaikan pesan-pesan kemanusiaan di sepanjang rute.
Saling Menjaga: Utamakan keselamatan bersama di atas kecepatan. Jika melihat peserta lain yang mengalami kendala fisik atau kelelahan, segera bantu atau laporkan kepada panitia/petugas medis terdekat.
Kepedulian Lingkungan: Dukung komitmen peduli sesama dengan menjaga kebersihan jalur lari. Buanglah gelas air minum atau sampah lainnya hanya pada tempat yang telah disediakan.

# 3. Ketentuan di Jalur Lari
Rute Resmi: Peserta wajib berlari atau berjalan di rute yang telah ditentukan oleh panitia demi keselamatan bersama.
Posisi Start: Atur barisan secara tertib saat di garis start. Pelari dengan kecepatan tinggi diharapkan mengambil posisi di depan, sementara pejalan kaki atau pelari santai berada di barisan belakang.
Arahan Petugas: Patuhi seluruh instruksi dari marshal (petugas jalur) dan panitia demi kelancaran arus lalu lintas lari.

# 4. Kesehatan & Keamanan
Kondisi Fisik: Peserta bertanggung jawab atas kesehatan diri sendiri dan memastikan kondisi tubuh dalam keadaan fit untuk mengikuti kegiatan.
Perlengkapan Medis Pribadi: Bagi peserta yang memiliki riwayat penyakit tertentu, diwajibkan membawa obat-obatan pribadi.
Larangan Khusus: Dilarang membawa barang-barang tajam, senjata, atau atribut promosi politik praktis yang dapat mengganggu nilai-nilai kemanusiaan universal acara ini.
TEXT,
        'group' => 'Tata Tertib',
    ],
];
