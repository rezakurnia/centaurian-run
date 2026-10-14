<?php

/**
 * Definisi setting global aplikasi Centaurian FunRun.
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
        'key'     => 'app_name',
        'label'   => 'Nama Aplikasi',
        'type'    => 'text',
        'default' => 'Centaurian FunRun',
        'group'   => 'Identitas Acara',
    ],
    [
        'key'     => 'event_name',
        'label'   => 'Nama Event',
        'type'    => 'text',
        'default' => 'Centaurian FunRun 2026',
        'group'   => 'Identitas Acara',
    ],
    [
        'key'     => 'event_tagline',
        'label'   => 'Tagline',
        'type'    => 'text',
        'default' => 'MOVE WITH FAITH, RUN WITH CARE',
        'group'   => 'Identitas Acara',
    ],
    [
        'key'     => 'event_year',
        'label'   => 'Tahun Acara',
        'type'    => 'number',
        'default' => '2026',
        'group'   => 'Identitas Acara',
    ],
    [
        'key'     => 'event_description',
        'label'   => 'Deskripsi Singkat',
        'type'    => 'textarea',
        'default' => '',
        'group'   => 'Identitas Acara',
    ],

    // ========================================================================
    // GRUP 2: KONTAK & SOSIAL MEDIA
    // ========================================================================
    [
        'key'     => 'contact_email',
        'label'   => 'Email Kontak',
        'type'    => 'email',
        'default' => 'info@centaurian.funrun',
        'group'   => 'Kontak & Sosial Media',
    ],
    [
        'key'     => 'contact_phone',
        'label'   => 'No. HP Panitia',
        'type'    => 'text',
        'default' => '+62 812-3456-7890',
        'group'   => 'Kontak & Sosial Media',
    ],
    [
        'key'     => 'whatsapp_url',
        'label'   => 'Link WhatsApp',
        'type'    => 'url',
        'default' => '',
        'group'   => 'Kontak & Sosial Media',
    ],
    [
        'key'     => 'instagram_url',
        'label'   => 'URL Instagram',
        'type'    => 'url',
        'default' => '',
        'group'   => 'Kontak & Sosial Media',
    ],
    [
        'key'     => 'facebook_url',
        'label'   => 'URL Facebook',
        'type'    => 'url',
        'default' => '',
        'group'   => 'Kontak & Sosial Media',
    ],

    // ========================================================================
    // GRUP 3: KONFIGURASI PENDAFTARAN
    // ========================================================================
    [
        'key'     => 'registration_open',
        'label'   => 'Pendaftaran Dibuka',
        'type'    => 'toggle',
        'default' => '1',
        'group'   => 'Konfigurasi Pendaftaran',
    ],
    [
        'key'     => 'max_participants',
        'label'   => 'Maks Peserta',
        'type'    => 'number',
        'default' => '500',
        'group'   => 'Konfigurasi Pendaftaran',
    ],
    [
        'key'     => 'registration_deadline',
        'label'   => 'Batas Akhir Pendaftaran',
        'type'    => 'date',
        'default' => '',
        'group'   => 'Konfigurasi Pendaftaran',
    ],
    [
        'key'     => 'auto_confirm_payment',
        'label'   => 'Auto Konfirmasi Paket Gratis',
        'type'    => 'toggle',
        'default' => '1',
        'group'   => 'Konfigurasi Pendaftaran',
    ],

    // ========================================================================
    // GRUP 4: KONFIGURASI SCAN
    // ========================================================================
    [
        'key'     => 'scan_cooldown_seconds',
        'label'   => 'Jeda Antar Scan (detik)',
        'type'    => 'number',
        'default' => '3',
        'group'   => 'Konfigurasi Scan',
    ],
    [
        'key'     => 'scan_sound_enabled',
        'label'   => 'Suara Saat Scan',
        'type'    => 'toggle',
        'default' => '1',
        'group'   => 'Konfigurasi Scan',
    ],
    [
        'key'     => 'scan_auto_clear_seconds',
        'label'   => 'Auto Clear Hasil Scan (detik)',
        'type'    => 'number',
        'default' => '5',
        'group'   => 'Konfigurasi Scan',
    ],
    [
        'key'     => 'event_start_hour',
        'label'   => 'Jam Start Acara',
        'type'    => 'text',
        'default' => '06:00',
        'group'   => 'Konfigurasi Scan',
    ],
    [
        'key'     => 'finish_button_visible_minutes',
        'label'   => 'Tombol Akhiri Muncul Setelah (menit)',
        'type'    => 'number',
        'default' => '60',
        'group'   => 'Konfigurasi Scan',
    ],

       // ========================================================================
    // GRUP 5: TAMPILAN & BRANDING
    // ========================================================================
    [
        'key'     => 'primary_color',
        'label'   => 'Warna Utama',
        'type'    => 'text',
        'default' => '#2563EB',
        'group'   => 'Tampilan & Branding',
    ],
    [
        'key'     => 'primary_light',
        'label'   => 'Warna Utama (Terang)',
        'type'    => 'text',
        'default' => '#3B82F6',
        'group'   => 'Tampilan & Branding',
    ],
    [
        'key'     => 'secondary_color',
        'label'   => 'Warna Sekunder (Lembut)',
        'type'    => 'text',
        'default' => '#EFF6FF',
        'group'   => 'Tampilan & Branding',
    ],
    [
        'key'     => 'accent_color',
        'label'   => 'Warna Aksen',
        'type'    => 'text',
        'default' => '#1E40AF',
        'group'   => 'Tampilan & Branding',
    ],
];