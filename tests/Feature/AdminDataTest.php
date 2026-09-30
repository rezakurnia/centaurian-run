<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminDataTest extends TestCase
{
    protected User $admin;
    protected User $panitia;
    protected string $tokenAdmin;
    protected string $tokenPanitia;

    protected Event $event;
    protected Category $categorySiswa;
    protected Category $categoryGuru;
    protected Package $package;
    protected Registration $reg1;
    protected Registration $reg2;

    protected function setUp(): void
    {
        parent::setUp();

        // Users
        $this->admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'email'    => 'admin@centaurian.test',
            'role'     => 'admin',
        ]);
        $this->tokenAdmin = $this->admin->createToken('test')->plainTextToken;

        $this->panitia = User::create([
            'username' => 'panitia',
            'password' => Hash::make('panitia123'),
            'email'    => 'panitia@centaurian.test',
            'role'     => 'panitia',
        ]);
        $this->tokenPanitia = $this->panitia->createToken('test')->plainTextToken;

        // Master data
        $this->event = Event::create([
            'name'       => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);
        $this->categorySiswa = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $this->categoryGuru  = Category::create(['name' => 'Guru Karyawan', 'code' => 'GK']);
        $this->package       = Package::create(['name' => 'Paket 100K', 'price' => 100000]);

        // Registrasi 1 (Siswa)
        $p1 = Participant::create([
            'full_name' => 'Budi Santoso',
            'email'     => 'budi@example.com',
        ]);
        $this->reg1 = Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number'     => 1,
            'barcode'             => 'BUDI-SW-0001',
            'participant_id'      => $p1->id,
            'event_id'            => $this->event->id,
            'category_id'         => $this->categorySiswa->id,
            'package_id'          => $this->package->id,
            'registration_status' => 'pending',
            'payment_status'      => 'unpaid',
        ]);

        // Registrasi 2 (Guru)
        $p2 = Participant::create([
            'full_name' => 'Ibu Sari',
            'email'     => 'sari@example.com',
        ]);
        $this->reg2 = Registration::create([
            'registration_number' => 'GK-0001',
            'sequence_number'     => 1,
            'barcode'             => 'SARI-GK-0001',
            'participant_id'      => $p2->id,
            'event_id'            => $this->event->id,
            'category_id'         => $this->categoryGuru->id,
            'package_id'          => $this->package->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'paid',
        ]);
    }

    private function adminHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->tokenAdmin}"];
    }

    private function panitiaHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->tokenPanitia}"];
    }

    public function test_daftar_peserta(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/participants');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_daftar_peserta_filter_kategori(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/participants?category_id=' . $this->categorySiswa->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.full_name', 'Budi Santoso');
    }

    public function test_daftar_peserta_filter_search_nama(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/participants?search=Sari');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.full_name', 'Ibu Sari');
    }

    public function test_daftar_pendaftaran(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/registrations');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_daftar_pendaftaran_filter_status(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/registrations?registration_status=confirmed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.registration_number', 'GK-0001');
    }

    public function test_detail_pendaftaran(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson("/api/admin/registrations/{$this->reg1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.registration_number', 'SW-0001');
    }

    public function test_detail_pendaftaran_tidak_ditemukan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/registrations/9999');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_verifikasi_pendaftaran(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/registrations/{$this->reg1->id}/verify", [
                'registration_status' => 'confirmed',
                'payment_status'      => 'paid',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.registration_status', 'confirmed')
            ->assertJsonPath('data.payment_status', 'paid');

        $this->assertDatabaseHas('registrations', [
            'id'                  => $this->reg1->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'paid',
            'verified_by'         => $this->admin->id,
        ]);
    }

    public function test_verifikasi_validasi_gagal(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/registrations/{$this->reg1->id}/verify", [
                'registration_status' => 'invalid_status',
                'payment_status'      => 'paid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registration_status']);
    }

    public function test_dashboard_ringkasan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_participants', 2)
            ->assertJsonPath('data.total_registrations', 2)
            ->assertJsonStructure([
                'data' => [
                    'total_participants',
                    'total_registrations',
                    'by_category',
                    'by_payment_status',
                    'by_registration_status',
                ],
            ]);
    }

    public function test_panitia_tidak_bisa_akses_data_admin(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/participants')
            ->assertStatus(403);

        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_verifikasi_mengirim_email(): void
{
    Mail::fake();

    $this->withHeaders($this->adminHeaders())
        ->putJson("/api/admin/registrations/{$this->reg1->id}/verify", [
            'registration_status' => 'confirmed',
            'payment_status'      => 'paid',
        ])
        ->assertStatus(200);

    Mail::assertSent(\App\Mail\RegistrationVerifiedMail::class, function ($mail) {
        return $mail->registration->id === $this->reg1->id;
    });
}


}