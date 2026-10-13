<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSettingTest extends TestCase
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
            'email' => 'admin@centaurian.test',
            'role' => 'admin',
        ]);
        $this->tokenAdmin = $this->admin->createToken('test')->plainTextToken;

        $this->panitia = User::create([
            'username' => 'panitia',
            'password' => Hash::make('panitia123'),
            'email' => 'panitia@centaurian.test',
            'role' => 'panitia',
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

    public function test_daftar_setting_kosong(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/settings');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_buat_setting_berhasil(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/settings', [
                'key' => 'app_name',
                'value' => 'Centaurian FunRun 2026',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.key', 'app_name')
            ->assertJsonPath('data.value', 'Centaurian FunRun 2026');

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name',
            'value' => 'Centaurian FunRun 2026',
        ]);
    }

    public function test_buat_setting_duplikat_key_ditolak(): void
    {
        Setting::create(['key' => 'app_name', 'value' => 'A']);

        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/settings', [
                'key' => 'app_name',
                'value' => 'B',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    public function test_buat_setting_validasi_gagal(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/settings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    public function test_detail_setting_by_key(): void
    {
        Setting::create(['key' => 'app_name', 'value' => 'Centaurian']);

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/settings/app_name');

        $response->assertStatus(200)
            ->assertJsonPath('data.key', 'app_name')
            ->assertJsonPath('data.value', 'Centaurian');
    }

    public function test_detail_setting_tidak_ditemukan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/settings/tidak_ada');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_update_setting_berhasil(): void
    {
        Setting::create(['key' => 'app_name', 'value' => 'Lama']);

        $response = $this->withHeaders($this->adminHeaders())
            ->putJson('/api/admin/settings/app_name', [
                'value' => 'Baru',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.value', 'Baru');

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name',
            'value' => 'Baru',
        ]);
    }

    public function test_hapus_setting_berhasil(): void
    {
        Setting::create(['key' => 'app_name', 'value' => 'Akan Dihapus']);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson('/api/admin/settings/app_name');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('settings', ['key' => 'app_name']);
    }

    public function test_panitia_tidak_bisa_akses_settings(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/settings')
            ->assertStatus(403);
    }

    public function test_tanpa_token_returns_401(): void
    {
        $this->getJson('/api/admin/settings')->assertStatus(401);
    }
}
