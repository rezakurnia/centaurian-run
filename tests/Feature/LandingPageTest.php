<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Setting;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::create([
            'name' => 'Centaurian Run',
            'event_date' => '2026-05-15',
            'is_active' => true,
        ]);

        Category::create(['name' => 'Siswa SMP', 'code' => 'S', 'quota' => 50]);
        Category::create(['name' => 'Umum', 'code' => 'A', 'quota' => 50]);

        // is_active defaults to true in the schema, so these are all part of the public price list
        Package::create([
            'name' => 'Gratis',
            'price' => 0,
            'description' => 'Hanya untuk Guru Karyawan dan siswa yang ditunjuk.',
        ]);
        Package::create([
            'name' => 'Paket 100K',
            'price' => 100000,
            'description' => 'Hanya untuk Siswa Alfa Centauri (SMP & SMA). Mendapatkan BIB, Racepack, Konsumsi, dan Medali.',
        ]);
        Package::create([
            'name' => 'Paket 150K',
            'price' => 150000,
            'description' => 'Untuk Orangtua dan Umum. Mendapatkan BIB, Racepack, Konsumsi, Medali, dan Kaos.',
        ]);

        $this->withoutVite();
    }

    public function test_kartu_paket_hanya_menampilkan_paket_berbayar_beserta_detailnya(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Paket 100K')
            ->assertSee('Rp100.000')
            ->assertSee('Paket 150K')
            ->assertSee('Rp150.000')
            ->assertSee('Mendapatkan BIB, Racepack, Konsumsi, dan Medali.')
            ->assertSee('Mendapatkan BIB, Racepack, Konsumsi, Medali, dan Kaos.')
            ->assertDontSee('Hanya untuk Guru Karyawan');
    }

    public function test_teks_landing_tidak_lagi_menyebut_alumni_atau_gratis(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Siswa, guru, orang tua, umum')
            ->assertDontSee('alumni')
            ->assertDontSee('mulai dari gratis');
    }

    public function test_tata_tertib_resmi_acara_tampil(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Tata tertib resmi acara.')
            ->assertSee('1. Pendaftaran & Kepesertaan (Move with Faith)')
            ->assertSee('2. Etika & Solidaritas (Run with Care)')
            ->assertSee('3. Ketentuan di Jalur Lari')
            ->assertSee('4. Kesehatan & Keamanan')
            ->assertSee('Nomor Dada (BIB)')
            ->assertSee('Larangan Khusus');
    }

    public function test_tata_tertib_mengikuti_pengaturan_admin(): void
    {
        Setting::create([
            'key' => 'tata_tertib',
            'value' => "# Bagian Baru\nLabel Baru: isi baru dari admin.",
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Bagian Baru')
            ->assertSee('isi baru dari admin.')
            ->assertDontSee('Nomor Dada (BIB)');
    }

    public function test_link_nav_rute_dan_tata_tertib_hanya_di_landing(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('#rute')
            ->assertSee('#tata-tertib');

        $this->get('/daftar')
            ->assertOk()
            ->assertSee('/#kategori')
            ->assertDontSee('#rute')
            ->assertDontSee('href="#tata-tertib"');
    }

    public function test_rute_5k_tampil_beserta_penandanya(): void
    {
        $this->assertFileExists(public_path('images/rute-5k.webp'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Rute 5K.')
            ->assertSee('/images/rute-5k.webp')
            ->assertSee('CENTAURIAN RUN')
            ->assertSee('Start / Finish')
            ->assertSee('Water station')
            ->assertSee('KM MARKER')
            ->assertSee('MARSHAL')
            ->assertDontSee('Penanda 2 km, di ruas jalan sisi barat.')
            ->assertDontSee('Pos air minum');
    }

    public function test_footer_kontak_dan_instagram(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('info.centaurian@alfacentauri.sch.id')
            ->assertSee('0821-1094-2273')
            ->assertSee('tel:082110942273')
            ->assertSee('https://www.instagram.com/centaurian_run')
            ->assertDontSee('info@centaurian.funrun')
            ->assertDontSee('812-3456-7890');
    }

    public function test_nama_funrun_sudah_tidak_ada_di_landing(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Centaurian Run')
            ->assertDontSee('FunRun');
    }
}
