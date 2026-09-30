<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Content;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    protected User $admin;
    protected string $tokenAdmin;

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
    }

    private function adminHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->tokenAdmin}"];
    }

    public function test_create_content_mencatat_activity_log(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/contents', [
                'target_site' => 'utama',
                'title'       => 'Test Konten',
                'status'      => 'published',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('activity_logs', [
            'user_id'      => $this->admin->id,
            'action'       => 'create_content',
            'target_table' => 'contents',
        ]);
    }

    public function test_update_content_mencatat_activity_log(): void
    {
        $content = Content::create([
            'target_site' => 'utama',
            'title'       => 'Judul Awal',
            'status'      => 'draft',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/contents/{$content->id}", [
                'title' => 'Judul Baru',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'update_content',
            'target_table' => 'contents',
            'target_id'    => $content->id,
        ]);
    }

    public function test_delete_content_mencatat_activity_log(): void
    {
        $content = Content::create([
            'target_site' => 'utama',
            'title'       => 'Akan Dihapus',
            'status'      => 'published',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->deleteJson("/api/admin/contents/{$content->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'delete_content',
            'target_table' => 'contents',
            'target_id'    => $content->id,
        ]);
    }

    public function test_create_event_mencatat_activity_log(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/events', [
                'name'       => 'Event Test',
                'event_date' => '2026-05-15',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'create_event',
            'target_table' => 'events',
        ]);
    }

    public function test_create_user_mencatat_activity_log(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/users', [
                'username' => 'panitia2',
                'password' => 'rahasia123',
                'role'     => 'panitia',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'create_user',
            'target_table' => 'users',
        ]);
    }

    public function test_activity_log_menyimpan_ip_dan_user_agent(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/contents', [
                'target_site' => 'utama',
                'title'       => 'Test',
                'status'      => 'published',
            ]);

        $log = ActivityLog::latest()->first();

        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
        $this->assertEquals($this->admin->id, $log->user_id);
    }

    public function test_activity_log_mencatat_detail(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/contents', [
                'target_site' => 'utama',
                'title'       => 'Konten Spesial',
                'status'      => 'published',
            ]);

        $log = ActivityLog::where('action', 'create_content')->latest()->first();

        $this->assertStringContainsString('Konten Spesial', $log->detail);
    }
}