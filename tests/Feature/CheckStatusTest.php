<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use Tests\TestCase;

class CheckStatusTest extends TestCase
{
    protected Event $event;

    protected Category $category;

    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'name' => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active' => true,
        ]);
        $this->category = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $this->package = Package::create(['name' => 'Paket 100K', 'price' => 100000]);
    }

    private function buatRegistrasi(
        string $email,
        string $noPeserta,
        string $barcode,
        string $status = 'pending',
        string $bayar = 'unpaid'
    ): Registration {
        $participant = Participant::create([
            'full_name' => 'Peserta '.$email,
            'email' => $email,
        ]);

        return Registration::create([
            'registration_number' => $noPeserta,
            'sequence_number' => 1,
            'barcode' => $barcode,
            'participant_id' => $participant->id,
            'event_id' => $this->event->id,
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
            'registration_status' => $status,
            'payment_status' => $bayar,
        ]);
    }

    public function test_cek_status_by_nomor_peserta(): void
    {
        $this->buatRegistrasi('budi@example.com', 'SW-0001', 'BUDI-SW-0001');

        $response = $this->postJson('/api/registrations/check-status', [
            'registration_number' => 'SW-0001',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.registration_number', 'SW-0001')
            ->assertJsonPath('data.0.registration_status', 'pending')
            ->assertJsonPath('data.0.payment_status', 'unpaid');
    }

    public function test_cek_status_by_email(): void
    {
        $this->buatRegistrasi('budi@example.com', 'SW-0001', 'BUDI-SW-0001');

        $response = $this->postJson('/api/registrations/check-status', [
            'email' => 'budi@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.registration_number', 'SW-0001');
    }

    public function test_cek_status_email_dengan_2_pendaftaran(): void
    {
        // Peserta sama daftar di 2 event
        $participant = Participant::create([
            'full_name' => 'Budi',
            'email' => 'budi@example.com',
        ]);

        $event2 = Event::create([
            'name' => 'FunRun 2027',
            'event_date' => '2027-05-15',
            'is_active' => false,
        ]);

        Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number' => 1,
            'barcode' => 'BUDI-SW-0001',
            'participant_id' => $participant->id,
            'event_id' => $this->event->id,
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
        ]);

        Registration::create([
            'registration_number' => 'SW-0002',
            'sequence_number' => 2,
            'barcode' => 'BUDI-SW-0002',
            'participant_id' => $participant->id,
            'event_id' => $event2->id,
            'category_id' => $this->category->id,
            'package_id' => $this->package->id,
        ]);

        $response = $this->postJson('/api/registrations/check-status', [
            'email' => 'budi@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_cek_status_tidak_ditemukan_returns_404(): void
    {
        $response = $this->postJson('/api/registrations/check-status', [
            'registration_number' => 'XX-9999',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('data', []);
    }

    public function test_cek_status_tanpa_field_returns_422(): void
    {
        $response = $this->postJson('/api/registrations/check-status', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_cek_status_butuh_email_valid(): void
    {
        $response = $this->postJson('/api/registrations/check-status', [
            'email' => 'bukan-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
