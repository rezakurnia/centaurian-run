<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanitiaTest extends TestCase
{
    protected User $panitia;

    protected string $token;

    protected Event $event;

    protected Category $categorySiswa;

    protected Category $categoryGuru;

    protected Package $package;

    protected Registration $regSiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panitia = User::create([
            'username' => 'panitia',
            'password' => Hash::make('panitia123'),
            'email' => 'panitia@centaurian.test',
            'role' => 'panitia',
        ]);
        $this->token = $this->panitia->createToken('test')->plainTextToken;

        $this->event = Event::create([
            'name' => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active' => true,
        ]);

        $this->categorySiswa = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $this->categoryGuru = Category::create(['name' => 'Guru Karyawan', 'code' => 'GK']);
        $this->categoryAlumni = Category::create(['name' => 'Alumni',   'code' => 'AL']);
        $this->categoryOrangtua = Category::create(['name' => 'Orangtua', 'code' => 'OT']);
        $this->categoryExternal = Category::create(['name' => 'External', 'code' => 'EX']);
        $this->package = Package::create(['name' => 'Paket 100K', 'price' => 100000]);

        // Registrasi Siswa #1
        $participant1 = Participant::create([
            'full_name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
        $this->regSiswa = Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number' => 1,
            'barcode' => 'BUDI-SW-0001',
            'participant_id' => $participant1->id,
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package->id,
            'registration_status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // Registrasi Siswa #2
        $participant2 = Participant::create([
            'full_name' => 'Andi Wijaya',
            'email' => 'andi@example.com',
        ]);
        Registration::create([
            'registration_number' => 'SW-0002',
            'sequence_number' => 2,
            'barcode' => 'ANDI-SW-0002',
            'participant_id' => $participant2->id,
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswa->id,
            'package_id' => $this->package->id,
            'registration_status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // Registrasi Guru #1
        $participant3 = Participant::create([
            'full_name' => 'Ibu Sari',
            'email' => 'sari@example.com',
        ]);
        Registration::create([
            'registration_number' => 'GK-0001',
            'sequence_number' => 1,
            'barcode' => 'SARI-GK-0001',
            'participant_id' => $participant3->id,
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
            'package_id' => $this->package->id,
            'registration_status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    private function headers(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_rekap_per_kategori(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/panitia/recap/category');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.name', 'Siswa')
            ->assertJsonPath('data.0.code', 'SW')
            ->assertJsonPath('data.0.total_participants', 2)
            ->assertJsonPath('data.1.name', 'Guru Karyawan')
            ->assertJsonPath('data.1.total_participants', 1);
    }

    public function test_rekap_per_nomor_peserta(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/panitia/recap/participants');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.registration_number', 'GK-0001')
            ->assertJsonPath('data.1.registration_number', 'SW-0001')
            ->assertJsonPath('data.2.registration_number', 'SW-0002');
    }

    public function test_start_acara_mengisi_start_time(): void
    {
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/panitia/start', ['event_id' => $this->event->id]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data' => ['event_id', 'start_time']]);

        $this->assertDatabaseHas('events', [
            'id' => $this->event->id,
        ]);
        $this->assertNotNull(Event::find($this->event->id)->start_time);
    }

    public function test_scan_valid(): void
    {
        // Start acara dulu
        $this->withHeaders($this->headers())
            ->postJson('/api/panitia/start', ['event_id' => $this->event->id]);

        $response = $this->withHeaders($this->headers())
            ->postJson('/api/panitia/scan', ['barcode' => 'BUDI-SW-0001']);

        $response->assertStatus(200)
            ->assertJsonPath('data.registration_number', 'SW-0001')
            ->assertJsonPath('data.full_name', 'Budi Santoso');

        $this->assertDatabaseHas('results', [
            'registration_id' => $this->regSiswa->id,
            'scan_status' => 'valid',
        ]);
    }

    public function test_scan_duplikat_returns_409(): void
    {
        $this->withHeaders($this->headers())
            ->postJson('/api/panitia/start', ['event_id' => $this->event->id]);

        $this->withHeaders($this->headers())
            ->postJson('/api/panitia/scan', ['barcode' => 'BUDI-SW-0001'])
            ->assertStatus(200);

        $this->withHeaders($this->headers())
            ->postJson('/api/panitia/scan', ['barcode' => 'BUDI-SW-0001'])
            ->assertStatus(409);
    }

    public function test_scan_invalid_returns_404(): void
    {
        $this->withHeaders($this->headers())
            ->postJson('/api/panitia/start', ['event_id' => $this->event->id]);

        $response = $this->withHeaders($this->headers())
            ->postJson('/api/panitia/scan', ['barcode' => 'TIDAK-ADA-999']);

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_scan_sebelum_start_returns_404(): void
    {
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/panitia/scan', ['barcode' => 'BUDI-SW-0001']);

        $response->assertStatus(404);
    }

    public function test_panitia_tidak_bisa_akses_endpoint_tanpa_token(): void
    {
        $this->getJson('/api/panitia/recap/category')->assertStatus(401);
    }
}
