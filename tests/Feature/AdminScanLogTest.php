<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ScanLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminScanLogTest extends TestCase
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

    private function buatLog(
        string $barcode,
        string $status,
        ?int $participantId = null,
        ?string $scannedAt = null
    ): ScanLog {
        return ScanLog::create([
            'participant_id' => $participantId,
            'barcode'        => $barcode,
            'scan_status'    => $status,
            'scanned_by'     => $this->panitia->id,
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'PHPUnit',
            'scanned_at'     => $scannedAt ?? now(),
        ]);
    }

    public function test_daftar_scan_log_kosong(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/scan-logs');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure(['message', 'data', 'meta']);
    }

    public function test_daftar_scan_log_menampilkan_semua(): void
    {
        $this->buatLog('BUDI-SW-0001', 'valid');
        $this->buatLog('TIDAK-ADA', 'invalid');
        $this->buatLog('BUDI-SW-0001', 'duplicate');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/scan-logs');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_filter_scan_status(): void
    {
        $this->buatLog('BUDI-SW-0001', 'valid');
        $this->buatLog('TIDAK-ADA', 'invalid');
        $this->buatLog('BUDI-SW-0001', 'duplicate');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/scan-logs?scan_status=valid');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.scan_status', 'valid');
    }

    public function test_filter_barcode(): void
    {
        $this->buatLog('BUDI-SW-0001', 'valid');
        $this->buatLog('SARI-GK-0001', 'valid');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/scan-logs?barcode=BUDI');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.barcode', 'BUDI-SW-0001');
    }

    public function test_filter_date_range(): void
    {
        $this->buatLog('A', 'valid', null, '2026-09-20 10:00:00');
        $this->buatLog('B', 'valid', null, '2026-09-25 10:00:00');
        $this->buatLog('C', 'valid', null, '2026-09-28 10:00:00');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/scan-logs?date_from=2026-09-24&date_to=2026-09-26');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.barcode', 'B');
    }

    public function test_detail_scan_log(): void
    {
        $participant = Participant::create([
            'full_name' => 'Budi Santoso',
            'email'     => 'budi@example.com',
        ]);

        $log = $this->buatLog('BUDI-SW-0001', 'valid', $participant->id);

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson("/api/admin/scan-logs/{$log->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.barcode', 'BUDI-SW-0001')
            ->assertJsonPath('data.scan_status', 'valid')
            ->assertJsonPath('data.participant.full_name', 'Budi Santoso')
            ->assertJsonPath('data.scanned_by.username', 'panitia');
    }

    public function test_detail_scan_log_tidak_ditemukan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/scan-logs/9999');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_scan_log_menampilkan_ip_dan_user_agent(): void
    {
        $log = $this->buatLog('BUDI-SW-0001', 'valid');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson("/api/admin/scan-logs/{$log->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.ip_address', '127.0.0.1')
            ->assertJsonPath('data.user_agent', 'PHPUnit');
    }

    public function test_panitia_tidak_bisa_akses_scan_logs(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/scan-logs')
            ->assertStatus(403);
    }

    public function test_tanpa_token_returns_401(): void
    {
        $this->getJson('/api/admin/scan-logs')->assertStatus(401);
    }
}