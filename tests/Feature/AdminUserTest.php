<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
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

    public function test_daftar_user(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'username', 'email', 'role'],
                ],
            ]);
    }

    public function test_daftar_user_tidak_menampilkan_password(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/users');

        $response->assertStatus(200);

        $json = $response->json();
        foreach ($json['data'] as $user) {
            $this->assertArrayNotHasKey('password', $user);
        }
    }

    public function test_buat_user_berhasil(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/users', [
                'username' => 'panitia2',
                'password' => 'rahasia123',
                'email'    => 'panitia2@centaurian.test',
                'role'     => 'panitia',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.username', 'panitia2')
            ->assertJsonPath('data.role', 'panitia');

        $this->assertDatabaseHas('users', [
            'username' => 'panitia2',
            'role'     => 'panitia',
        ]);
    }

    public function test_buat_user_validasi_gagal(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password', 'role']);
    }

    public function test_buat_user_username_duplikat_ditolak(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/users', [
                'username' => 'admin',
                'password' => 'rahasia123',
                'email'    => 'duplikat@centaurian.test',
                'role'     => 'panitia',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_buat_user_password_terlalu_pendek(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/users', [
                'username' => 'panitia3',
                'password' => '123',
                'email'    => 'panitia3@centaurian.test',
                'role'     => 'panitia',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_detail_user(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson("/api/admin/users/{$this->panitia->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'panitia');
    }

    public function test_detail_user_tidak_ditemukan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/users/9999');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_update_user_berhasil(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/users/{$this->panitia->id}", [
                'email' => 'panitia_new@centaurian.test',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'    => $this->panitia->id,
            'email' => 'panitia_new@centaurian.test',
        ]);
    }

    public function test_update_user_password_berubah(): void
    {
        $oldHash = $this->panitia->password;

        $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/users/{$this->panitia->id}", [
                'password' => 'passwordbaru123',
            ])
            ->assertStatus(200);

        $updated = User::find($this->panitia->id);
        $this->assertNotEquals($oldHash, $updated->password);
        $this->assertTrue(Hash::check('passwordbaru123', $updated->password));
    }

    public function test_hapus_user_berhasil(): void
    {
        $userBaru = User::create([
            'username' => 'untuk_hapus',
            'password' => Hash::make('rahasia123'),
            'email'    => 'hapus@centaurian.test',
            'role'     => 'panitia',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson("/api/admin/users/{$userBaru->id}");

        $response->assertStatus(200);

        // Soft delete
        $this->assertSoftDeleted('users', ['id' => $userBaru->id]);
    }

    public function test_admin_tidak_bisa_hapus_diri_sendiri(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson("/api/admin/users/{$this->admin->id}");

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Tidak dapat menghapus akun sendiri.');

        // Pastikan admin masih ada
        $this->assertDatabaseHas('users', [
            'id'       => $this->admin->id,
            'username' => 'admin',
        ]);
    }

    public function test_panitia_tidak_bisa_akses_kelola_user(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/users')
            ->assertStatus(403);

        $this->withHeaders($this->panitiaHeaders())
            ->postJson('/api/admin/users', [
                'username' => 'test',
                'password' => 'rahasia123',
                'role'     => 'panitia',
            ])
            ->assertStatus(403);
    }

    public function test_tanpa_token_returns_401(): void
    {
        $this->getJson('/api/admin/users')->assertStatus(401);
    }
}