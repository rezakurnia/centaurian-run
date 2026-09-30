<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Result;
use Tests\TestCase;

class ResultTest extends TestCase
{
    protected Event $event;
    protected Category $categorySiswa;
    protected Category $categoryGuru;
    protected Package $package;
    protected Registration $regSiswa;
    protected Registration $regGuru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'name'       => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'start_time' => now()->subMinutes(30),
            'is_active'  => true,
        ]);

        $this->categorySiswa = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $this->categoryGuru  = Category::create(['name' => 'Guru Karyawan', 'code' => 'GK']);
        $this->package       = Package::create(['name' => 'Paket 100K', 'price' => 100000]);

        // Registrasi Siswa
        $participantSiswa = Participant::create([
            'full_name' => 'Budi Santoso',
            'email'     => 'budi@example.com',
        ]);
        $this->regSiswa = Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number'     => 1,
            'barcode'             => 'BUDI-SW-0001',
            'participant_id'      => $participantSiswa->id,
            'event_id'            => $this->event->id,
            'category_id'         => $this->categorySiswa->id,
            'package_id'          => $this->package->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'paid',
        ]);

        // Registrasi Guru
        $participantGuru = Participant::create([
            'full_name' => 'Ibu Sari',
            'email'     => 'sari@example.com',
        ]);
        $this->regGuru = Registration::create([
            'registration_number' => 'GK-0001',
            'sequence_number'     => 1,
            'barcode'             => 'SARI-GK-0001',
            'participant_id'      => $participantGuru->id,
            'event_id'            => $this->event->id,
            'category_id'         => $this->categoryGuru->id,
            'package_id'          => $this->package->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'paid',
        ]);
    }

    private function createResult(Registration $reg, int $duration, string $status = 'valid'): Result
    {
        return Result::create([
            'registration_id' => $reg->id,
            'participant_id'  => $reg->participant_id,
            'event_id'        => $reg->event_id,
            'start_time'      => now()->subMinutes(30),
            'finish_time'     => now()->subMinutes(30)->addSeconds($duration),
            'duration'        => $duration,
            'scan_status'     => $status,
        ]);
    }

    public function test_daftar_results_menampilkan_hasil_scan_valid(): void
    {
        $this->createResult($this->regSiswa, 220);
        $this->createResult($this->regGuru, 300);

        $response = $this->getJson('/api/results');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.registration_number', 'SW-0001')
            ->assertJsonPath('data.0.full_name', 'Budi Santoso')
            ->assertJsonPath('data.0.category', 'Siswa')
            ->assertJsonPath('data.0.duration', 220)
            ->assertJsonPath('data.0.scan_status', 'valid');
    }

    public function test_hasil_diurutkan_berdasarkan_duration_tercepat(): void
    {
        $this->createResult($this->regSiswa, 220);
        $this->createResult($this->regGuru, 150);

        $response = $this->getJson('/api/results');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.registration_number', 'GK-0001')  // duration 150
            ->assertJsonPath('data.1.registration_number', 'SW-0001'); // duration 220
    }

    public function test_filter_kategori(): void
    {
        $this->createResult($this->regSiswa, 220);
        $this->createResult($this->regGuru, 300);

        $response = $this->getJson('/api/results?category_id=' . $this->categorySiswa->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.registration_number', 'SW-0001');
    }

    public function test_scan_status_invalid_tidak_muncul(): void
    {
        $this->createResult($this->regSiswa, 220, 'valid');
        $this->createResult($this->regGuru, 300, 'invalid');

        $response = $this->getJson('/api/results');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.registration_number', 'SW-0001');
    }

    public function test_result_by_no_peserta_berhasil(): void
    {
        $this->createResult($this->regSiswa, 220);

        $response = $this->getJson('/api/results/SW-0001');

        $response->assertStatus(200)
            ->assertJsonPath('data.registration_number', 'SW-0001')
            ->assertJsonPath('data.full_name', 'Budi Santoso')
            ->assertJsonPath('data.duration', 220);
    }

    public function test_result_by_no_peserta_tidak_ditemukan(): void
    {
        $response = $this->getJson('/api/results/XX-9999');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }
}