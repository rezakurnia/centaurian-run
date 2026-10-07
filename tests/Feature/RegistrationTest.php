<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    protected Event $event;

    protected Category $categorySiswa;

    protected Category $categoryGuru;

    protected Package $packageGratis;

    protected Package $package100k;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed data yang dibutuhkan
        $this->event = Event::create([
            'name' => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active' => true,
        ]);

        $this->categorySiswa = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $this->categoryGuru = Category::create(['name' => 'Guru Karyawan', 'code' => 'GK']);

        $this->packageGratis = Package::create(['name' => 'Gratis', 'price' => 0]);
        $this->package100k = Package::create(['name' => 'Paket 100K', 'price' => 100000]);
    }

    public function test_registrasi_berhasil_dan_penomoran_otomatis(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Budi Santoso',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '2000-05-15',
            'motivation' => 'Tetap semangat!',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.registration_number', 'SW-0001')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'participant' => ['id', 'full_name', 'email'],
                    'registration' => ['id', 'registration_number', 'barcode', 'category', 'package', 'event'],
                ],
            ]);

        $this->assertDatabaseHas('registrations', [
            'registration_number' => 'SW-0001',
        ]);
    }

    public function test_penomoran_per_kategori_terpisah(): void
    {
        // Siswa #1
        $this->postJson('/api/registrations', [
            'full_name' => 'Budi',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '2000-05-15',
            'email' => 'budi@example.com',
            'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ]);

        // Siswa #2
        $this->postJson('/api/registrations', [
            'full_name' => 'Andi',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '2000-05-15',
            'email' => 'andi@example.com',
            'phone' => '08124',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ]);

        // Guru #1 → harus GK-0001, bukan SW-0003
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Ibu Sari',
            'gender' => 'P',
            'birth_place' => 'Jakarta',
            'birth_date' => '1985-03-10',
            'email' => 'sari@example.com',
            'phone' => '08125',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
            'package_id' => $this->packageGratis->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.registration_number', 'GK-0001');

        $this->assertDatabaseHas('registrations', ['registration_number' => 'SW-0001']);
        $this->assertDatabaseHas('registrations', ['registration_number' => 'SW-0002']);
        $this->assertDatabaseHas('registrations', ['registration_number' => 'GK-0001']);
    }

    public function test_barcode_unik(): void
    {
        $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ]);

        $this->postJson('/api/registrations', [
            'full_name' => 'Andi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'andi@example.com', 'phone' => '08124',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ]);

        $barcodes = Registration::pluck('barcode')->toArray();
        $this->assertCount(2, array_unique($barcodes));
    }

    public function test_validasi_gagal_jika_field_kosong(): void
    {
        $response = $this->postJson('/api/registrations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'full_name', 'gender', 'birth_place', 'birth_date',
                'email', 'phone', 'event_id', 'category_id', 'package_id',
            ]);
    }

    public function test_duplikat_email_di_event_sama_ditolak(): void
    {
        $payload = [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ];

        $this->postJson('/api/registrations', $payload)->assertStatus(201);
        $this->postJson('/api/registrations', $payload)->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_email_sama_boleh_daftar_di_event_berbeda(): void
    {
        $event2 = Event::create([
            'name' => 'Centaurian FunRun 2027',
            'event_date' => '2027-05-15',
            'is_active' => false,
        ]);

        $payload = [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ];

        $this->postJson('/api/registrations', array_merge($payload, [
            'event_id' => $this->event->id,
        ]))->assertStatus(201);

        $this->postJson('/api/registrations', array_merge($payload, [
            'event_id' => $event2->id,
        ]))->assertStatus(201);

        // Peserta hanya 1 (email sama)
        $this->assertEquals(1, Participant::count());
        // Tapi registrasi ada 2
        $this->assertEquals(2, Registration::count());
    }

    public function test_paket_gratis_otomatis_payment_status_free(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Guru Test',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '1985-05-15',
            'email' => 'guru@example.com',
            'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
            'package_id' => $this->packageGratis->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.payment_status', 'free')
            ->assertJsonPath('data.registration.registration_status', 'confirmed');

        $this->assertDatabaseHas('registrations', [
            'payment_status' => 'free',
            'registration_status' => 'confirmed',
        ]);
    }

    public function test_paket_berbayar_tetap_unpaid(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Siswa Test',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '2000-05-15',
            'email' => 'siswa@example.com',
            'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package100k->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.payment_status', 'unpaid')
            ->assertJsonPath('data.registration.registration_status', 'pending');
    }
}
