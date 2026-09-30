<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
   

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'email'    => 'admin@centaurian.test',
            'role'     => 'admin',
        ]);

        User::create([
            'username' => 'panitia',
            'password' => Hash::make('panitia123'),
            'email'    => 'panitia@centaurian.test',
            'role'     => 'panitia',
        ]);
    }

    public function test_login_admin_berhasil(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'username', 'email', 'role'],
                'token',
            ])
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_login_panitia_berhasil(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'panitia',
            'password' => 'panitia123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.role', 'panitia');
    }

    public function test_login_password_salah_ditolak(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'salah',
        ]);

        $response->assertStatus(422);
    }

    public function test_me_butuh_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_me_dengan_token_berhasil(): void
    {
        $user = User::where('username', 'admin')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonPath('username', 'admin');
    }

    public function test_logout_menghapus_token(): void
    {
        $user = User::where('username', 'admin')->first();
        $token = $user->createToken('test')->plainTextToken;

        $logout = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $logout->assertStatus(200);

        // Cek token sudah dihapus dari database
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}