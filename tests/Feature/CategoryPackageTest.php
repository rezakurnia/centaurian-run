<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Package;
use Tests\TestCase;

class CategoryPackageTest extends TestCase
{
    public function test_daftar_kategori_publik(): void
    {
        Category::create(['name' => 'Siswa', 'code' => 'SW']);
        Category::create(['name' => 'Guru Karyawan', 'code' => 'GK']);
        Category::create(['name' => 'Alumni', 'code' => 'AL']);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.name', 'Siswa')
            ->assertJsonPath('data.0.code', 'SW')
            ->assertJsonStructure([
                'message',
                'data' => [['id', 'name', 'code']],
            ]);
    }

    public function test_daftar_kategori_kosong(): void
    {
        $this->getJson('/api/categories')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_daftar_paket_publik(): void
    {
        Package::create(['name' => 'Gratis', 'price' => 0]);
        Package::create(['name' => 'Paket 100K', 'price' => 100000]);
        Package::create(['name' => 'Paket 150K', 'price' => 150000]);

        $response = $this->getJson('/api/packages');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.name', 'Gratis')
            ->assertJsonPath('data.0.price', 0)
            ->assertJsonStructure([
                'message',
                'data' => [['id', 'name', 'price', 'description']],
            ]);
    }

    public function test_daftar_paket_diurutkan_berdasarkan_harga(): void
    {
        Package::create(['name' => 'Paket 150K', 'price' => 150000]);
        Package::create(['name' => 'Gratis', 'price' => 0]);
        Package::create(['name' => 'Paket 100K', 'price' => 100000]);

        $response = $this->getJson('/api/packages');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.price', 0)
            ->assertJsonPath('data.1.price', 100000)
            ->assertJsonPath('data.2.price', 150000);
    }

    public function test_kategori_paket_tidak_butuh_token(): void
    {
        $this->getJson('/api/categories')->assertStatus(200);
        $this->getJson('/api/packages')->assertStatus(200);
    }
}
