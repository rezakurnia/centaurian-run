<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    protected Event $event;
    protected Category $category;
    protected Package $packageGratis;
    protected Package $package100k;
    protected Registration $regBerbayar;
    protected Registration $regGratis;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->event = Event::create([
            'name'       => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);
        $this->category = Category::create(['name' => 'Siswa', 'code' => 'SW']);
        $this->packageGratis = Package::create(['name' => 'Gratis', 'price' => 0]);
        $this->package100k   = Package::create(['name' => 'Paket 100K', 'price' => 100000]);

        $p1 = Participant::create([
            'full_name' => 'Budi Santoso',
            'email'     => 'budi@example.com',
        ]);
        $this->regBerbayar = Registration::create([
            'registration_number' => 'SW-0001',
            'sequence_number'     => 1,
            'barcode'             => 'BUDI-SW-0001',
            'participant_id'      => $p1->id,
            'event_id'            => $this->event->id,
            'category_id'         => $this->category->id,
            'package_id'          => $this->package100k->id,
            'registration_status' => 'pending',
            'payment_status'      => 'unpaid',
        ]);

        $p2 = Participant::create([
            'full_name' => 'Ibu Sari',
            'email'     => 'sari@example.com',
        ]);
        $this->regGratis = Registration::create([
            'registration_number' => 'SW-0002',
            'sequence_number'     => 2,
            'barcode'             => 'SARI-SW-0002',
            'participant_id'      => $p2->id,
            'event_id'            => $this->event->id,
            'category_id'         => $this->category->id,
            'package_id'          => $this->packageGratis->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'free',
        ]);
    }

    public function test_upload_bukti_bayar_berhasil(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg', 600, 800);

        $response = $this->postJson(
            "/api/registrations/{$this->regBerbayar->registration_number}/payment-proof",
            ['payment_proof' => $file]
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.payment_status', 'unpaid')
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'registration_number', 'payment_proof', 'payment_proof_url'],
            ]);

        $this->assertNotNull($this->regBerbayar->fresh()->payment_proof);
        Storage::disk('public')->assertExists($this->regBerbayar->fresh()->payment_proof);
    }

    public function test_upload_tanpa_file_gagal(): void
    {
        $response = $this->postJson(
            "/api/registrations/{$this->regBerbayar->registration_number}/payment-proof",
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_proof']);
    }

    public function test_upload_file_format_salah_gagal(): void
    {
        $file = UploadedFile::fake()->create('dokumen.txt', 100);

        $response = $this->postJson(
            "/api/registrations/{$this->regBerbayar->registration_number}/payment-proof",
            ['payment_proof' => $file]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_proof']);
    }

    public function test_upload_untuk_registrasi_tidak_ada_returns_404(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->postJson(
            "/api/registrations/XX-9999/payment-proof",
            ['payment_proof' => $file]
        );

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_upload_untuk_paket_gratis_ditolak(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->postJson(
            "/api/registrations/{$this->regGratis->registration_number}/payment-proof",
            ['payment_proof' => $file]
        );

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Paket gratis tidak memerlukan bukti pembayaran.');
    }
}