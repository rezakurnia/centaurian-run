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

class AdminEventTest extends TestCase
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

    public function test_daftar_event_kosong(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/events');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_buat_event_berhasil(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/events', [
                'name'        => 'Centaurian FunRun 2026',
                'description' => 'Lari santai',
                'location'    => 'Lapangan Sekolah',
                'event_date'  => '2026-05-15',
                'is_active'   => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Centaurian FunRun 2026')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('events', ['name' => 'Centaurian FunRun 2026']);
    }

    public function test_buat_event_validasi_gagal(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/events', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'event_date']);
    }

    public function test_detail_event(): void
    {
        $event = Event::create([
            'name'       => 'Event Test',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson("/api/admin/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Event Test');
    }

    public function test_detail_event_tidak_ditemukan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/events/9999');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_update_event_berhasil(): void
    {
        $event = Event::create([
            'name'       => 'Nama Lama',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/events/{$event->id}", [
                'name'     => 'Nama Baru',
                'location' => 'Lokasi Baru',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.location', 'Lokasi Baru');
    }

    public function test_hapus_event_berhasil(): void
    {
        $event = Event::create([
            'name'       => 'Akan Dihapus',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson("/api/admin/events/{$event->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_tidak_bisa_hapus_event_yang_sudah_ada_peserta(): void
    {
        $event = Event::create([
            'name'       => 'Event Aktif',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);
        $category = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $package  = Package::create(['name' => 'Gratis', 'price' => 0]);
        $peserta  = Participant::create([
            'full_name' => 'Budi',
            'email'     => 'budi@example.com',
        ]);
        Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number'     => 1,
            'barcode'             => 'BUDI-SW-0001',
            'participant_id'      => $peserta->id,
            'event_id'            => $event->id,
            'category_id'         => $category->id,
            'package_id'          => $package->id,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson("/api/admin/events/{$event->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_toggle_active_berhasil(): void
    {
        $event = Event::create([
            'name'       => 'Event Test',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/events/{$event->id}/toggle-active");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        // Toggle lagi
        $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/events/{$event->id}/toggle-active")
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', true);
    }

    public function test_panitia_tidak_bisa_akses_crud_event(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/events')
            ->assertStatus(403);

        $this->withHeaders($this->panitiaHeaders())
            ->postJson('/api/admin/events', [
                'name'       => 'Test',
                'event_date' => '2026-05-15',
            ])
            ->assertStatus(403);
    }
}