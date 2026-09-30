<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Event;
use Tests\TestCase;

class EventContentTest extends TestCase
{
    public function test_event_aktif_muncul(): void
    {
        Event::create([
            'name'       => 'Centaurian FunRun 2026',
            'location'   => 'Lapangan Sekolah',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);

        Event::create([
            'name'       => 'Event Lama',
            'event_date' => '2020-01-01',
            'is_active'  => false,
        ]);

        $response = $this->getJson('/api/event/active');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Centaurian FunRun 2026')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'description', 'location', 'event_date', 'start_time', 'is_active'],
            ]);
    }

    public function test_event_aktif_kosong_returns_404(): void
    {
        $response = $this->getJson('/api/event/active');

        $response->assertStatus(404)
            ->assertJsonPath('data', null);
    }

    public function test_konten_website_utama_muncul(): void
    {
        Content::create([
            'target_site' => 'utama',
            'section'     => 'banner',
            'title'       => 'Selamat Datang',
            'body'        => 'Ikuti Centaurian FunRun!',
            'status'      => 'published',
            'sort_order'  => 1,
        ]);

        Content::create([
            'target_site' => 'utama',
            'section'     => 'info',
            'title'       => 'Info Acara',
            'body'        => 'Detail acara...',
            'status'      => 'published',
            'sort_order'  => 2,
        ]);

        $response = $this->getJson('/api/contents');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Selamat Datang')
            ->assertJsonPath('data.1.title', 'Info Acara');
    }

    public function test_konten_draft_tidak_muncul(): void
    {
        Content::create([
            'target_site' => 'utama',
            'section'     => 'banner',
            'title'       => 'Draft',
            'status'      => 'draft',
        ]);

        $response = $this->getJson('/api/contents');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_konten_panitia_tidak_muncul_di_utama(): void
    {
        Content::create([
            'target_site' => 'panitia',
            'section'     => 'info',
            'title'       => 'Info Panitia',
            'status'      => 'published',
        ]);

        $response = $this->getJson('/api/contents');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}