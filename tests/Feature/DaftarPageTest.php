<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use Tests\TestCase;

class DaftarPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::create([
            'name' => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active' => true,
        ]);

        Category::create(['name' => 'Siswa SMP', 'code' => 'S', 'quota' => 50]);
        Category::create(['name' => 'Umum', 'code' => 'A', 'quota' => 50]);

        // routes/web.php maps category code to package id (S => 2), so package 2 must carry the price
        Package::create(['name' => 'Gratis', 'price' => 0, 'description' => 'Paket gratis tanpa biaya.']);
        Package::create(['name' => 'Paket 100K', 'price' => 100000, 'description' => 'Hanya untuk Siswa Alfa Centauri (SMP & SMA).']);

        $this->withoutVite();
    }

    public function test_kartu_pilihan_kategori_hanya_menampilkan_nama(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('Siswa SMP')
            ->assertSee('Umum')
            ->assertDontSee('Rp100.000')
            ->assertDontSee('Hanya untuk Siswa Alfa Centauri');
    }

    public function test_kuota_ditampilkan_di_ringkasan(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('sum-quota')
            ->assertDontSee('data-quota');
    }

    public function test_dialog_qr_ada_di_halaman_daftar(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('id="barcode-dialog"', false)
            ->assertSee('id="success-qr"', false);
    }
}
