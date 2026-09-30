<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessTest extends TestCase
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

    /**
     * ADMIN BISA AKSES ENDPOINT ADMIN
     */
    public function test_admin_bisa_akses_dashboard(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/dashboard')
            ->assertStatus(200);
    }

    public function test_admin_bisa_akses_kelola_user(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/users')
            ->assertStatus(200);
    }

    public function test_admin_bisa_akses_kelola_konten(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/contents')
            ->assertStatus(200);
    }

    /**
     * ADMIN JUGA BISA AKSES ENDPOINT PANITIA
     * (karena admin punya hak lebih tinggi)
     */
    public function test_admin_bisa_akses_endpoint_panitia(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->getJson('/api/panitia/recap/category')
            ->assertStatus(200);
    }

    /**
     * PANITIA TIDAK BISA AKSES ENDPOINT ADMIN
     */
    public function test_panitia_tidak_bisa_akses_dashboard(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/dashboard')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Unauthorized. Role tidak sesuai.');
    }

    public function test_panitia_tidak_bisa_akses_kelola_user(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/users')
            ->assertStatus(403);
    }

    public function test_panitia_tidak_bisa_akses_kelola_konten(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/contents')
            ->assertStatus(403);
    }

    public function test_panitia_tidak_bisa_akses_data_peserta(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/participants')
            ->assertStatus(403);
    }

    public function test_panitia_tidak_bisa_akses_data_pendaftaran(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/registrations')
            ->assertStatus(403);
    }

    /**
     * PANITIA BISA AKSES ENDPOINT PANITIA
     */
    public function test_panitia_bisa_akses_rekap(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/panitia/recap/category')
            ->assertStatus(200);
    }

    /**
     * TANPA TOKEN → 401
     */
    public function test_tanpa_token_ke_endpoint_admin_returns_401(): void
    {
        $this->getJson('/api/admin/dashboard')->assertStatus(401);
        $this->getJson('/api/admin/users')->assertStatus(401);
        $this->getJson('/api/admin/contents')->assertStatus(401);
    }

    public function test_tanpa_token_ke_endpoint_panitia_returns_401(): void
    {
        $this->getJson('/api/panitia/recap/category')->assertStatus(401);
    }

    /**
     * TOKEN INVALID → 401
     */
    public function test_token_invalid_returns_401(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer token-palsu-123'])
            ->getJson('/api/admin/dashboard')
            ->assertStatus(401);
    }

    /**
     * ENDPOINT PUBLIK TIDAK BUTUH TOKEN
     */
    public function test_endpoint_publik_tanpa_token_berhasil(): void
    {
        // Tidak ada event aktif → 404, tapi bukan 401
        $this->getJson('/api/event/active')->assertStatus(404);
        $this->getJson('/api/contents')->assertStatus(200);
        $this->getJson('/api/results')->assertStatus(200);
    }
}