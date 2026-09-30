<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Result;
use App\Models\ScanLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminResetEventTest extends TestCase
{
    protected User $admin;
    protected User $panitia;
    protected string $tokenAdmin;
    protected string $tokenPanitia;
    protected Event $event;
    protected Registration $registration;

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

        // Seed data
        $this->event = Event::create([
            'name'       => 'FunRun 2026',
            'event_date' => '2026-05-15',
            'start_time' => now(),
            'is_active'  => true,
        ]);
        $category = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $package  = Package::create(['name' => 'Gratis', 'price' => 0]);

        $participant = Participant::create([
            'full_name' => 'Budi',
            'email'     => 'budi@example.com',
        ]);

        $this->registration = Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number'     => 1,
            'barcode'             => 'BUDI-SW-0001',
            'participant_id'      => $participant->id,
            'event_id'            => $this->event->id,
            'category_id'         => $category->id,
            'package_id'          => $package->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'free',
        ]);

        // Buat result & scan_log
        Result::create([
            'registration_id' => $this->registration->id,
            'participant_id'  => $participant->id,
            'event_id'        => $this->event->id,
            'start_time'      => now()->subMinutes(30),
            'finish_time'     => now(),
            'duration'        => 1800,
            'scan_status'     => 'valid',
        ]);

        ScanLog::create([
            'participant_id' => $participant->id,
            'barcode'        => 'BUDI-SW-0001',
            'scan_status'    => 'valid',
            'scanned_by'     => $this->panitia->id,
            'scanned_at'     => now(),
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

    public function test_reset_acara_berhasil(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/admin/events/{$this->event->id}/reset", [
                'confirmation' => 'RESET',
                'password'     => 'admin123',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.event_id', $this->event->id)
            ->assertJsonStructure([
                'message',
                'data' => ['scan_logs_deleted', 'results_deleted', 'event_id'],
            ]);

        // Cek start_time sudah null
        $this->assertNull($this->event->fresh()->start_time);

        // Cek results sudah kosong
        $this->assertDatabaseCount('results', 0);

        // Cek scan_logs sudah kosong
        $this->assertDatabaseCount('scan_logs', 0);

        // Cek registrations TETAP ada (Opsi A)
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_reset_gagal_tanpa_konfirmasi(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/admin/events/{$this->event->id}/reset", [
                'password' => 'admin123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);
    }

    public function test_reset_gagal_konfirmasi_salah(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/admin/events/{$this->event->id}/reset", [
                'confirmation' => 'reset',
                'password'     => 'admin123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);
    }

    public function test_reset_gagal_password_salah(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/admin/events/{$this->event->id}/reset", [
                'confirmation' => 'RESET',
                'password'     => 'passwordsalah',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_gagal_tanpa_password(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/admin/events/{$this->event->id}/reset", [
                'confirmation' => 'RESET',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_event_tidak_ada_returns_404(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/events/9999/reset', [
                'confirmation' => 'RESET',
                'password'     => 'admin123',
            ]);

        $response->assertStatus(404);
    }

    public function test_reset_mencatat_activity_log(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->postJson("/api/admin/events/{$this->event->id}/reset", [
                'confirmation' => 'RESET',
                'password'     => 'admin123',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'reset_event',
            'target_table' => 'events',
            'target_id'    => $this->event->id,
        ]);
    }

    public function test_panitia_tidak_bisa_reset(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->postJson("/api/admin/events/{$this->event->id}/reset", [
                'confirmation' => 'RESET',
                'password'     => 'panitia123',
            ])
            ->assertStatus(403);
    }

    public function test_tanpa_token_returns_401(): void
    {
        $this->postJson("/api/admin/events/{$this->event->id}/reset", [
            'confirmation' => 'RESET',
            'password'     => 'admin123',
        ])->assertStatus(401);
    }
}
