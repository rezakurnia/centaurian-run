<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminActivityLogTest extends TestCase
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
        string $action,
        ?int $userId = null,
        ?string $table = null,
        ?string $createdAt = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id'      => $userId ?? $this->admin->id,
            'action'       => $action,
            'target_table' => $table,
            'target_id'    => 1,
            'detail'       => "Detail untuk {$action}",
            'ip_address'   => '127.0.0.1',
            'user_agent'   => 'PHPUnit',
            'created_at'   => $createdAt ?? now(),
        ]);
    }

    public function test_daftar_activity_log_kosong(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure(['message', 'data', 'meta']);
    }

    public function test_daftar_activity_log_menampilkan_semua(): void
    {
        $this->buatLog('create_content', null, 'contents');
        $this->buatLog('update_content', null, 'contents');
        $this->buatLog('create_user', null, 'users');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_filter_by_action(): void
    {
        $this->buatLog('create_content', null, 'contents');
        $this->buatLog('update_content', null, 'contents');
        $this->buatLog('create_user', null, 'users');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs?action=create');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_filter_by_target_table(): void
    {
        $this->buatLog('create_content', null, 'contents');
        $this->buatLog('create_user', null, 'users');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs?target_table=users');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.target_table', 'users');
    }

    public function test_filter_by_user_id(): void
    {
        $this->buatLog('create_content', $this->admin->id, 'contents');
        $this->buatLog('update_content', $this->panitia->id, 'contents');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs?user_id=' . $this->panitia->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user.username', 'panitia');
    }

    public function test_filter_by_date_range(): void
    {
        $this->buatLog('action_a', null, null, '2026-09-20 10:00:00');
        $this->buatLog('action_b', null, null, '2026-09-25 10:00:00');
        $this->buatLog('action_c', null, null, '2026-09-28 10:00:00');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs?date_from=2026-09-24&date_to=2026-09-26');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.action', 'action_b');
    }

    public function test_detail_activity_log(): void
    {
        $log = $this->buatLog('create_content', $this->admin->id, 'contents');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson("/api/admin/activity-logs/{$log->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.action', 'create_content')
            ->assertJsonPath('data.target_table', 'contents')
            ->assertJsonPath('data.user.username', 'admin')
            ->assertJsonPath('data.ip_address', '127.0.0.1')
            ->assertJsonPath('data.user_agent', 'PHPUnit');
    }

    public function test_detail_activity_log_tidak_ditemukan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs/9999');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_activity_log_diurutkan_desc(): void
    {
        $this->buatLog('action_lama', null, null, '2026-09-20 10:00:00');
        $this->buatLog('action_baru', null, null, '2026-09-25 10:00:00');

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/activity-logs');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.action', 'action_baru');
    }

    public function test_panitia_tidak_bisa_akses_activity_logs(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/activity-logs')
            ->assertStatus(403);
    }

    public function test_tanpa_token_returns_401(): void
    {
        $this->getJson('/api/admin/activity-logs')->assertStatus(401);
    }
}