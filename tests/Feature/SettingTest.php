<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingTest extends TestCase
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

    public function test_public_settings_returns_flat_array(): void
    {
        $response = $this->getJson('/api/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'app_name',
                    'event_name',
                    'event_tagline',
                    'contact_email',
                    'registration_open',
                ],
            ]);
    }

    public function test_admin_settings_returns_grouped_array(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'Identitas Acara',
                    'Kontak & Sosial Media',
                    'Konfigurasi Pendaftaran',
                    'Konfigurasi Scan',
                ],
            ]);
    }

    public function test_admin_settings_grouped_has_correct_structure(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/settings');

        $response->assertStatus(200);

        $data = $response->json('data');
        $identitas = $data['Identitas Acara'] ?? [];

        $this->assertNotEmpty($identitas);
        $this->assertArrayHasKey('key', $identitas[0]);
        $this->assertArrayHasKey('label', $identitas[0]);
        $this->assertArrayHasKey('type', $identitas[0]);
        $this->assertArrayHasKey('value', $identitas[0]);
    }

    public function test_bulk_update_saves_settings(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->putJson('/api/admin/settings/bulk-update', [
                'settings' => [
                    'app_name'            => 'My FunRun App',
                    'contact_email'       => 'admin@example.com',
                    'max_participants'    => '1000',
                ],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('settings', [
            'key'   => 'app_name',
            'value' => 'My FunRun App',
        ]);
        $this->assertDatabaseHas('settings', [
            'key'   => 'contact_email',
            'value' => 'admin@example.com',
        ]);
        $this->assertDatabaseHas('settings', [
            'key'   => 'max_participants',
            'value' => '1000',
        ]);
    }

    public function test_bulk_update_validation_gagal(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->putJson('/api/admin/settings/bulk-update', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['settings']);
    }

    public function test_panitia_tidak_bisa_bulk_update(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->tokenPanitia}",
        ])->putJson('/api/admin/settings/bulk-update', [
            'settings' => ['app_name' => 'Hacked'],
        ]);

        $response->assertStatus(403);
    }

    public function test_bulk_update_mencatat_activity_log(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->putJson('/api/admin/settings/bulk-update', [
                'settings' => [
                    'app_name' => 'Test App',
                ],
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'update_settings',
            'target_table' => 'settings',
        ]);
    }

    public function test_public_settings_tidak_butuh_token(): void
    {
        $this->getJson('/api/settings')->assertStatus(200);
    }

    public function test_admin_settings_butuh_token(): void
    {
        $this->getJson('/api/admin/settings')->assertStatus(401);
    }
}