<?php

namespace Tests\Feature;

use App\Mail\RegistrationConfirmationMail;
use App\Mail\RegistrationVerifiedMail;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Package;
use App\Models\Registration;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    private const SERVER_KEY = 'SB-Mid-server-test';

    protected Event $event;

    protected Category $categoryGuru;

    protected Category $categoryUmum;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'name' => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active' => true,
        ]);

        // Package ids are resolved from the category code in RegistrationController
        Package::create(['name' => 'Gratis', 'price' => 0, 'is_active' => true]);
        Package::create(['name' => 'Paket 100K', 'price' => 100000, 'is_active' => true]);
        Package::create(['name' => 'Paket 150K', 'price' => 150000, 'is_active' => true]);

        Category::create(['name' => 'Siswa SMP', 'code' => 'S', 'quota' => 50]);
        $this->categoryGuru = Category::create(['name' => 'Guru Karyawan', 'code' => 'E', 'quota' => 300]);
        Category::create(['name' => 'Orangtua', 'code' => 'P', 'quota' => 100]);
        $this->categoryUmum = Category::create(['name' => 'Umum', 'code' => 'A', 'quota' => 50]);

        config([
            'services.midtrans.server_key' => self::SERVER_KEY,
            'services.midtrans.client_key' => 'SB-Mid-client-test',
            'services.midtrans.is_production' => false,
        ]);
    }

    public function test_paket_berbayar_langsung_mendapat_halaman_pembayaran_snap(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())
            ->assertCreated()
            ->assertJsonPath('payment.redirect_url', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123');

        $registration = Registration::sole();

        $this->assertSame('A-0001', $registration->registration_number);
        $this->assertSame('snap-token-123', $registration->snap_token);
        $this->assertSame('unpaid', $registration->payment_status);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $request->hasHeader('Authorization')
                && $request['transaction_details']['order_id'] === 'A-0001'
                && $request['transaction_details']['gross_amount'] === 150000
                && $request['item_details'][0]['price'] === 150000
                && $request['callbacks']['finish'] === url('/pembayaran/selesai/A-0001');
        });
    }

    public function test_paket_gratis_tidak_membuat_transaksi_snap(): void
    {
        Http::fake();
        Mail::fake();
        Employee::create(['nip' => '14000012', 'name' => 'Miqdad Nasrul Haq']);

        $this->postJson('/api/registrations', [
            'full_name' => 'Miqdad Nasrul Haq', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '1990-01-01',
            'email' => 'miqdad@example.com', 'phone' => '08123',
            'nip' => '14000012', 'position' => 'Guru', 'unit' => 'SMP',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
        ])->assertCreated()
            ->assertJsonPath('payment', null);

        $this->assertNull(Registration::sole()->snap_token);
        Http::assertNothingSent();

        // Free registrations are confirmed right away, so the number can go out immediately
        Mail::assertSent(RegistrationConfirmationMail::class);
    }

    public function test_email_konfirmasi_menunggu_pembayaran_untuk_paket_berbayar(): void
    {
        Mail::fake();
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        Mail::assertNotSent(RegistrationConfirmationMail::class);
    }

    public function test_order_id_yang_sudah_terpakai_diulang_dengan_akhiran_baru(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::sequence()
                ->push(['error_messages' => ['transaction_details.order_id sudah digunakan']], 400)
                ->push(['token' => 'snap-token-123', 'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123'], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())
            ->assertCreated()
            ->assertJsonPath('payment.redirect_url', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123');

        $this->assertMatchesRegularExpression('/^A-0001-R[a-z0-9]{5}$/', Registration::sole()->payment_order_id);
    }

    public function test_snap_gagal_membatalkan_pendaftaran_berbayar(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response(['status_message' => 'unauthorized'], 401),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())
            ->assertStatus(503);

        $this->assertSame(0, Registration::count());
    }

    public function test_kunci_sandbox_tanpa_prefix_sb_tetap_memakai_endpoint_sandbox(): void
    {
        // Sebagian merchant mendapat kunci sandbox tanpa prefix "SB-": yang menentukan environment
        // adalah MIDTRANS_IS_PRODUCTION, bukan bentuk kuncinya
        config([
            'services.midtrans.server_key' => 'Mid-server-dummy-sandbox-key',
            'services.midtrans.is_production' => false,
        ]);
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-sandbox',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-sandbox',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())
            ->assertCreated()
            ->assertJsonPath('payment.redirect_url', 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-sandbox');

        Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://app.sandbox.midtrans.com/'));
    }

    public function test_status_pendaftaran_membawa_link_pembayaran_untuk_yang_belum_lunas(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/registrations/check-status', ['registration_number' => 'A-0001'])
            ->assertOk()
            ->assertJsonPath('data.0.payment_url', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123');
    }

    public function test_notifikasi_settlement_menandai_lunas_dan_mengirim_email(): void
    {
        Mail::fake();
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001'))
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.registration_status', 'confirmed');

        $registration = Registration::sole();

        $this->assertSame('paid', $registration->payment_status);
        $this->assertSame('confirmed', $registration->registration_status);
        $this->assertSame('bank_transfer', $registration->payment_method);
        $this->assertNotNull($registration->paid_at);
        Mail::assertSent(RegistrationVerifiedMail::class);
    }

    public function test_status_pembayaran_menyusul_dari_midtrans_tanpa_webhook(): void
    {
        Mail::fake();
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
            'api.sandbox.midtrans.com/*' => Http::response([
                'order_id' => 'A-0001',
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'payment_type' => 'qris',
                'status_code' => '200',
                'gross_amount' => '150000.00',
            ], 200),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/registrations/A-0001/payment-status')
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'paid');

        $registration = Registration::sole();

        $this->assertSame('qris', $registration->payment_method);
        $this->assertNotNull($registration->paid_at);
        Mail::assertSent(RegistrationVerifiedMail::class);

        $this->withoutVite();
        $this->get('/pembayaran/selesai/A-0001')
            ->assertOk()
            ->assertSee('Pendaftaran berhasil.')
            ->assertSee('A-0001');
    }

    public function test_status_pembayaran_pending_tetap_belum_lunas(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
            'api.sandbox.midtrans.com/*' => Http::response([
                'order_id' => 'A-0001',
                'transaction_status' => 'pending',
                'status_code' => '201',
                'gross_amount' => '150000.00',
            ], 200),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/registrations/A-0001/payment-status')
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'unpaid');
    }

    public function test_status_transaction_not_found_tetap_belum_lunas(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
            'api.sandbox.midtrans.com/*' => Http::response([
                'status_code' => '404',
                'status_message' => 'Transaction not found',
            ], 200),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/registrations/A-0001/payment-status')
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'unpaid');
    }

    public function test_status_pembayaran_tanpa_snap_token_tidak_memanggil_status_api(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
            'api.sandbox.midtrans.com/*' => Http::response([
                'order_id' => 'A-0001',
                'transaction_status' => 'settlement',
                'status_code' => '200',
                'gross_amount' => '150000.00',
            ], 200),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();
        Registration::sole()->update(['snap_token' => null]);

        $this->postJson('/api/registrations/A-0001/payment-status')
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'unpaid');

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.sandbox.midtrans.com'));
    }

    public function test_notifikasi_pending_tidak_mengubah_status_pembayaran(): void
    {
        Mail::fake();
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001', 'pending'))
            ->assertOk();

        $registration = Registration::sole();

        $this->assertSame('unpaid', $registration->payment_status);
        $this->assertNull($registration->paid_at);
        Mail::assertNotSent(RegistrationVerifiedMail::class);
    }

    private function fakeSnap(): void
    {
        Mail::fake();
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
            'api.sandbox.midtrans.com/*' => Http::response(['status_message' => 'Transaction not found.', 'status_code' => '404'], 404),
        ]);
    }

    public function test_notifikasi_expire_melepas_kuota_dan_membuka_bayar_ulang(): void
    {
        $this->fakeSnap();
        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001', 'expire'))->assertOk();

        $registration = Registration::sole();
        $this->assertSame('cancelled', $registration->registration_status);
        $this->assertSame('unpaid', $registration->payment_status);
        $this->assertNotNull($registration->payment_expired_at);

        $this->postJson('/api/registrations/check-status', ['registration_number' => 'A-0001'])
            ->assertJsonPath('data.0.can_retry_payment', true)
            ->assertJsonPath('data.0.payment_url', null);
    }

    public function test_bayar_ulang_membuat_order_baru_dan_mengambil_kuota_lagi(): void
    {
        $this->fakeSnap();
        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();
        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001', 'expire'))->assertOk();

        $this->postJson('/api/registrations/A-0001/payment-retry')
            ->assertOk()
            ->assertJsonPath('payment.redirect_url', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123');

        $registration = Registration::sole();
        $this->assertSame('pending', $registration->registration_status);
        $this->assertNull($registration->payment_expired_at);
        $this->assertMatchesRegularExpression('/^A-0001-R[a-z0-9]{5}$/', $registration->payment_order_id);

        // The first attempt expiring later must not cancel the new one
        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001', 'expire'))->assertOk();
        $this->assertSame('pending', $registration->fresh()->registration_status);

        // Paying the new attempt confirms it
        $this->postJson('/api/payments/midtrans/notification', $this->notification($registration->payment_order_id))->assertOk();
        $this->assertSame('paid', $registration->fresh()->payment_status);
        $this->assertSame('confirmed', $registration->fresh()->registration_status);
    }

    public function test_bayar_ulang_ditolak_saat_kuota_penuh(): void
    {
        $this->fakeSnap();
        $this->categoryUmum->update(['quota' => 1]);
        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();
        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001', 'expire'))->assertOk();

        $other = $this->umumPayload();
        $other['email'] = 'lain@example.com';
        $other['phone'] = '081200000099';
        $this->postJson('/api/registrations', $other)->assertCreated();

        $this->postJson('/api/registrations/A-0001/payment-retry')->assertStatus(422);
        $this->assertSame('cancelled', Registration::where('registration_number', 'A-0001')->first()->registration_status);
    }

    public function test_pembayaran_terlambat_menghidupkan_lagi_pendaftaran_yang_lepas(): void
    {
        $this->fakeSnap();
        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();
        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001', 'expire'))->assertOk();

        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001'))->assertOk();

        $registration = Registration::sole();
        $this->assertSame('paid', $registration->payment_status);
        $this->assertSame('confirmed', $registration->registration_status);
        Mail::assertSent(RegistrationVerifiedMail::class, 1);
    }

    public function test_pembayaran_untuk_pendaftaran_dibatalkan_panitia_tidak_menghidupkannya(): void
    {
        $this->fakeSnap();
        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();
        Registration::sole()->update(['registration_status' => 'cancelled']);

        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001'))->assertOk();

        $this->assertSame('cancelled', Registration::sole()->registration_status);
        $this->assertSame('paid', Registration::sole()->payment_status);
        Mail::assertNotSent(RegistrationVerifiedMail::class);
    }

    public function test_notifikasi_ganda_hanya_mengirim_satu_email(): void
    {
        $this->fakeSnap();
        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001'))->assertOk();
        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001'))->assertOk();

        Mail::assertSent(RegistrationVerifiedMail::class, 1);
    }

    public function test_command_melepas_pembayaran_yang_kedaluwarsa(): void
    {
        $this->fakeSnap();
        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();
        Registration::sole()->update(['payment_expires_at' => now()->subHour()]);

        $this->artisan('payments:release-lapsed')->assertSuccessful();

        $registration = Registration::sole();
        $this->assertSame('cancelled', $registration->registration_status);
        $this->assertNotNull($registration->payment_expired_at);
    }

    public function test_notifikasi_dengan_signature_salah_ditolak(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $payload = $this->notification('A-0001');
        $payload['signature_key'] = str_repeat('0', 128);

        $this->postJson('/api/payments/midtrans/notification', $payload)
            ->assertForbidden();

        $this->assertSame('unpaid', Registration::sole()->payment_status);
    }

    public function test_notifikasi_untuk_pendaftaran_tak_dikenal_mengembalikan_404(): void
    {
        $this->postJson('/api/payments/midtrans/notification', $this->notification('Z-9999'))
            ->assertNotFound();
    }

    public function test_halaman_selesai_menunggu_konfirmasi_sebelum_dibayar(): void
    {
        $this->withoutVite();
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $this->get('/pembayaran/selesai/A-0001')
            ->assertOk()
            ->assertSee('Pembayaran sedang kami verifikasi')
            ->assertSee('Selesaikan pembayaranmu.')
            ->assertDontSee('Pendaftaran berhasil.');
    }

    public function test_halaman_selesai_menampilkan_nomor_setelah_pembayaran_lunas(): void
    {
        $this->withoutVite();
        Mail::fake();
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();
        $this->postJson('/api/payments/midtrans/notification', $this->notification('A-0001'))->assertOk();

        $this->get('/pembayaran/selesai/A-0001')
            ->assertOk()
            ->assertSee('Pendaftaran berhasil.')
            ->assertSee('A-0001')
            ->assertSee('Lunas')
            ->assertSee('id="barcode-dialog"', false)
            ->assertDontSee(Registration::first()->barcode);
    }

    public function test_halaman_selesai_untuk_nomor_tak_dikenal(): void
    {
        $this->withoutVite();

        $this->get('/pembayaran/selesai/Z-9999')
            ->assertNotFound()
            ->assertSee('Pendaftaran tidak ditemukan');
    }

    public function test_email_verifikasi_bisa_dirender(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        $this->postJson('/api/registrations', $this->umumPayload())->assertCreated();

        $registration = Registration::with(['participant', 'category', 'package', 'event'])->sole();
        $html = (new RegistrationVerifiedMail($registration))->render();

        $this->assertStringContainsString('A-0001', $html);
        $this->assertStringContainsString('Lunas', $html);
    }

    /**
     * @return array<string, mixed>
     */
    private function umumPayload(): array
    {
        return [
            'full_name' => 'Ibu Sari', 'gender' => 'P',
            'birth_place' => 'Jakarta', 'birth_date' => '1985-03-10',
            'email' => 'sari@example.com', 'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryUmum->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function notification(string $orderId, string $status = 'settlement', string $gross = '150000.00'): array
    {
        return [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $gross,
            'signature_key' => hash('sha512', $orderId.'200'.$gross.self::SERVER_KEY),
            'transaction_status' => $status,
            'fraud_status' => 'accept',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'trx-123',
            'transaction_time' => now()->toDateTimeString(),
        ];
    }
}
