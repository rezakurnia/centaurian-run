<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Admin\ExportController;
use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\User;
use App\Services\ScanService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditHardeningTest extends TestCase
{
    private function admin(): array
    {
        $user = User::create([
            'username' => 'admin', 'password' => Hash::make('admin123'),
            'email' => 'admin@centaurian.test', 'role' => 'admin',
        ]);

        return ['Authorization' => 'Bearer '.$user->createToken('t')->plainTextToken];
    }

    private function registration(string $status = 'confirmed', string $pay = 'paid', ?Event $event = null): Registration
    {
        $event ??= Event::create(['name' => 'Run', 'event_date' => '2026-05-15']);
        $category = Category::firstOrCreate(['code' => 'SW'], ['name' => 'Siswa']);
        $package = Package::firstOrCreate(['name' => 'Paket'], ['price' => 100000]);
        $participant = Participant::create(['full_name' => 'Budi', 'email' => 'budi@example.com', 'phone' => '0812']);

        return Registration::create([
            'registration_number' => 'SW-0001', 'sequence_number' => 1, 'barcode' => 'BARCODE-1',
            'participant_id' => $participant->id, 'event_id' => $event->id,
            'category_id' => $category->id, 'package_id' => $package->id,
            'registration_status' => $status, 'payment_status' => $pay,
        ]);
    }

    public function test_check_status_hanya_mengembalikan_data_publik(): void
    {
        $this->registration();

        $row = $this->postJson('/api/registrations/check-status', ['registration_number' => 'SW-0001'])
            ->assertOk()->json('data.0');

        $this->assertTrue($row['bib_ready']);
        $this->assertSame('Budi', $row['full_name']);
        foreach (['email', 'phone', 'barcode', 'id'] as $secret) {
            $this->assertArrayNotHasKey($secret, $row);
        }
        $this->assertArrayNotHasKey('email', $row['participant']);
    }

    public function test_event_baru_aktif_dan_event_lain_dinonaktifkan(): void
    {
        $headers = $this->admin();
        $old = Event::create(['name' => 'Lama', 'event_date' => '2025-05-15']);
        $this->assertTrue($old->fresh()->is_active);

        $id = $this->postJson('/api/admin/events', ['name' => 'Baru', 'event_date' => '2026-05-15'], $headers)
            ->assertCreated()->json('data.id');

        $this->assertTrue(Event::find($id)->is_active);
        $this->assertFalse($old->fresh()->is_active);
    }

    public function test_scan_menolak_barcode_belum_lunas_atau_dibatalkan(): void
    {
        $event = Event::create(['name' => 'Run', 'event_date' => '2026-05-15', 'start_time' => now()]);
        $reg = $this->registration('pending', 'unpaid', $event);
        $user = User::create(['username' => 'p', 'password' => 'x', 'email' => 'p@x.test', 'role' => 'panitia']);

        $out = app(ScanService::class)->scan('BARCODE-1', $user->id, null, null);
        $this->assertSame('invalid', $out['status']);

        $reg->update(['registration_status' => 'cancelled', 'payment_status' => 'paid']);
        $out = app(ScanService::class)->scan('BARCODE-1', $user->id, null, null);
        $this->assertSame('invalid', $out['status']);
    }

    public function test_start_acara_dua_kali_ditolak(): void
    {
        $user = User::create(['username' => 'p', 'password' => Hash::make('x'), 'email' => 'p@x.test', 'role' => 'panitia']);
        $headers = ['Authorization' => 'Bearer '.$user->createToken('t')->plainTextToken];
        $event = Event::create(['name' => 'Run', 'event_date' => '2026-05-15']);

        $this->postJson('/api/panitia/start', ['event_id' => $event->id], $headers)->assertOk();
        $this->postJson('/api/panitia/start', ['event_id' => $event->id], $headers)->assertStatus(422);
    }

    public function test_csv_menetralkan_sel_berawalan_rumus(): void
    {
        $this->assertSame(["'=SUM(A1)", "'+1", "'-1", "'@x", 'aman', 5, null], ExportController::safe(['=SUM(A1)', '+1', '-1', '@x', 'aman', 5, null]));
    }

    public function test_setting_yang_dikosongkan_tetap_kosong(): void
    {
        Setting::create(['key' => 'event_tagline', 'value' => '']);

        $this->assertSame('', app(SettingService::class)->getAllFlat()['event_tagline']);
    }
}
