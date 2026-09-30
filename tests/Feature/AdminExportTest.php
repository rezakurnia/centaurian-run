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

class AdminExportTest extends TestCase
{
    protected User $admin;
    protected User $panitia;
    protected string $tokenAdmin;
    protected string $tokenPanitia;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    private function adminHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->tokenAdmin}"];
    }

    private function panitiaHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->tokenPanitia}"];
    }

    private function seedData(): void
    {
        $event    = Event::create(['name' => 'FunRun 2026', 'event_date' => '2026-05-15', 'is_active' => true]);
        $category = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $package  = Package::create(['name' => 'Paket 100K', 'price' => 100000]);

        $p1 = Participant::create([
            'full_name' => 'Budi Santoso',
            'email'     => 'budi@example.com',
            'phone'     => '08123',
        ]);

        Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number'     => 1,
            'barcode'             => 'BUDI-SW-0001',
            'participant_id'      => $p1->id,
            'event_id'            => $event->id,
            'category_id'         => $category->id,
            'package_id'          => $package->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'paid',
        ]);
    }

    public function test_export_participants_returns_csv(): void
    {
        $this->seedData();

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/api/admin/export/participants');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Nama', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
    }

    public function test_export_registrations_returns_csv(): void
    {
        $this->seedData();

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/api/admin/export/registrations');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('No Peserta', $content);
        $this->assertStringContainsString('SW-0001', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
    }

    public function test_export_participants_filter_kategori(): void
    {
        $this->seedData();

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/api/admin/export/participants?category_id=1');

        $response->assertStatus(200);
    }

    public function test_export_registrations_filter_status(): void
    {
        $this->seedData();

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/api/admin/export/registrations?payment_status=paid');

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringContainsString('SW-0001', $content);
    }

    public function test_export_tanpa_data_tetap_csv_header(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->get('/api/admin/export/participants');

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nama', $content);
    }

    public function test_panitia_tidak_bisa_export(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->get('/api/admin/export/participants')
            ->assertStatus(403);
    }

    public function test_tanpa_token_returns_401(): void
{
    $this->getJson('/api/admin/export/participants')->assertStatus(401);
}
}