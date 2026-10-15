<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\User;
use App\Services\ParticipantImportService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParticipantImportTest extends TestCase
{
    protected User $admin;
    protected Event $event;
    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'email'    => 'admin@centaurian.test',
            'role'     => 'admin',
        ]);

        $this->event = Event::create([
            'name'       => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active'  => true,
        ]);

        $this->package = Package::create(['name' => 'Gratis', 'price' => 0]);
    }

    /**
     * Jalankan import.
     *
     * Baris yang dikirim memakai key persis seperti hasil normalisasi
     * ParticipantImportController atas heading template resmi:
     * "Nama Lengkap" → nama_lengkap, "No HP" → no_hp,
     * "Jenis Kelamin (L/P)" → jenis_kelamin_l_p, "Kelas" → kelas.
     */
    private function import(array $rows, string $type): array
    {
        return app(ParticipantImportService::class)->import(
            $rows,
            $type,
            $this->event->id,
            $this->package->id,
            $this->admin->id
        );
    }

    public function test_import_siswa_dengan_heading_template_tersimpan_lengkap(): void
    {
        Category::create(['name' => 'Siswa SMP', 'code' => 'S', 'quota' => 50]);

        $result = $this->import([[
            'nis'               => '12345',
            'nama_lengkap'      => 'Budi Santoso',
            'jenis_kelamin_l_p' => 'L',
            'no_hp'             => '08123456789',
            'email'             => 'budi@example.com',
            'kategori_siswa'    => 'Siswa',
            'kelas'             => '5A',
        ]], 'siswa');

        $this->assertSame(1, $result['success_count']);
        $this->assertSame([], $result['errors']);

        $participant = Participant::where('email', 'budi@example.com')->first();

        $this->assertNotNull($participant);
        $this->assertSame('Budi Santoso', $participant->full_name);
        $this->assertSame('L', $participant->gender);
        $this->assertSame('08123456789', $participant->phone);
        $this->assertSame('5A', $participant->class);
        $this->assertSame('12345', $participant->nis);
    }

    public function test_import_guru_dengan_heading_template_tersimpan_lengkap(): void
    {
        Category::create(['name' => 'Guru Karyawan', 'code' => 'E', 'quota' => 300]);

        $result = $this->import([[
            'nip'                    => 'NIP001',
            'nama_lengkap'           => 'Ibu Sari',
            'jenis_kelamin_l_p'      => 'P',
            'no_hp'                  => '08123456780',
            'email'                  => 'sari@example.com',
            'kategori_guru_karyawan' => 'Guru',
            'unit'                   => 'SD',
        ]], 'guru');

        $this->assertSame(1, $result['success_count']);

        $participant = Participant::where('email', 'sari@example.com')->first();

        $this->assertNotNull($participant);
        $this->assertSame('Ibu Sari', $participant->full_name);
        $this->assertSame('P', $participant->gender);
        $this->assertSame('08123456780', $participant->phone);
        $this->assertSame('NIP001', $participant->nip);
        $this->assertSame('SD', $participant->unit);
    }

    public function test_import_membuat_registrasi_confirmed_dan_nomor_peserta(): void
    {
        Category::create(['name' => 'Siswa SMP', 'code' => 'S', 'quota' => 50]);

        $this->import([[
            'nis'               => '111',
            'nama_lengkap'      => 'Andi',
            'jenis_kelamin_l_p' => 'L',
            'no_hp'             => '0812',
            'email'             => 'andi@example.com',
            'kelas'             => '6B',
        ]], 'siswa');

        $this->assertDatabaseHas('registrations', [
            'registration_number' => 'S-0001',
            'event_id'            => $this->event->id,
            'registration_status' => 'confirmed',
            'payment_status'      => 'free',
        ]);
    }

    public function test_import_email_duplikat_di_skip(): void
    {
        Category::create(['name' => 'Siswa SMP', 'code' => 'S', 'quota' => 50]);

        $row = [
            'nis'               => '111',
            'nama_lengkap'      => 'Budi',
            'jenis_kelamin_l_p' => 'L',
            'no_hp'             => '0812',
            'email'             => 'budi@example.com',
            'kelas'             => '6B',
        ];

        $this->import([$row], 'siswa');
        $result = $this->import([$row], 'siswa');

        $this->assertSame(1, $result['skipped_count']);
        $this->assertSame(1, Participant::count());
    }
}
