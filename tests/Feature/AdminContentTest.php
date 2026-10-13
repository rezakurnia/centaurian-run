<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminContentTest extends TestCase
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

    public function test_daftar_konten_awal_kosong(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/contents');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_buat_konten_berhasil(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/contents', [
                'target_site' => 'utama',
                'section' => 'banner',
                'title' => 'Selamat Datang',
                'body' => 'Ikuti Centaurian FunRun!',
                'status' => 'published',
                'sort_order' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Selamat Datang')
            ->assertJsonStructure(['message', 'data' => ['id', 'section', 'title', 'body', 'sort_order']]);

        $this->assertDatabaseHas('contents', [
            'target_site' => 'utama',
            'title' => 'Selamat Datang',
            'status' => 'published',
        ]);
    }

    public function test_buat_konten_validasi_gagal(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/contents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_site', 'status']);
    }

    public function test_detail_konten(): void
    {
        $content = Content::create([
            'target_site' => 'utama',
            'section' => 'banner',
            'title' => 'Judul Awal',
            'status' => 'published',
            'sort_order' => 1,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson("/api/admin/contents/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Judul Awal');
    }

    public function test_detail_konten_tidak_ditemukan(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/contents/9999');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_update_konten_berhasil(): void
    {
        $content = Content::create([
            'target_site' => 'utama',
            'title' => 'Judul Lama',
            'status' => 'draft',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/contents/{$content->id}", [
                'title' => 'Judul Baru',
                'status' => 'published',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Judul Baru');

        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'title' => 'Judul Baru',
            'status' => 'published',
        ]);
    }

    public function test_hapus_konten_berhasil(): void
    {
        $content = Content::create([
            'target_site' => 'utama',
            'title' => 'Akan Dihapus',
            'status' => 'published',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson("/api/admin/contents/{$content->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('contents', ['id' => $content->id]);
    }

    public function test_panitia_tidak_bisa_akses_crud_konten(): void
    {
        $this->withHeaders($this->panitiaHeaders())
            ->getJson('/api/admin/contents')
            ->assertStatus(403);

        $this->withHeaders($this->panitiaHeaders())
            ->postJson('/api/admin/contents', [
                'target_site' => 'utama',
                'status' => 'published',
            ])
            ->assertStatus(403);
    }

    public function test_tanpa_token_returns_401(): void
    {
        $this->getJson('/api/admin/contents')->assertStatus(401);
    }
}
