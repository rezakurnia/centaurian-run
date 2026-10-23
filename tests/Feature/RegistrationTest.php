<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Package;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    protected Event $event;

    protected Category $categorySiswaSMP;

    protected Category $categorySiswaSMA;

    protected Category $categoryGuru;

    protected Category $categoryOrangtua;

    protected Category $categoryUmum;

    protected Package $packageGratis;

    protected Package $package100k;

    protected Package $package150k;

    protected function setUp(): void
    {
        parent::setUp();

        // A paid registration needs a Snap page, otherwise it is rolled back
        config(['services.midtrans.server_key' => 'SB-Mid-server-test']);
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token',
            ], 201),
        ]);

        $this->event = Event::create([
            'name' => 'Centaurian FunRun 2026',
            'event_date' => '2026-05-15',
            'is_active' => true,
        ]);

        $this->categorySiswaSMP = Category::create(['name' => 'Siswa SMP', 'code' => 'S', 'quota' => 50]);
        $this->categorySiswaSMA = Category::create(['name' => 'Siswa SMA', 'code' => 'S', 'quota' => 150]);
        $this->categoryGuru = Category::create(['name' => 'Guru Karyawan', 'code' => 'E', 'quota' => 300]);
        $this->categoryOrangtua = Category::create(['name' => 'Orangtua', 'code' => 'P', 'quota' => 100]);
        $this->categoryUmum = Category::create(['name' => 'Umum', 'code' => 'A', 'quota' => 50]);

        $this->packageGratis = Package::create(['name' => 'Gratis', 'price' => 0, 'is_active' => true]);
        $this->package100k = Package::create(['name' => 'Paket 100K', 'price' => 100000, 'is_active' => true]);
        $this->package150k = Package::create(['name' => 'Paket 150K', 'price' => 150000, 'is_active' => true]);

        // Guru/karyawan NIPs must exist in the employee master (EmployeeSeeder in production)
        Employee::create(['nip' => 'NIP001', 'name' => 'Bu Sari']);
    }

    // ========================================================================
    // REGISTRASI BERHASIL
    // ========================================================================

    public function test_registrasi_berhasil_dan_penomoran_otomatis(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Budi Santoso',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '2000-05-15',
            'motivation' => 'Tetap semangat!',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'nis' => '12345',
            'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.registration_number', 'S-0001')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'participant' => ['id', 'full_name', 'email'],
                    'registration' => ['id', 'registration_number', 'barcode', 'category', 'package', 'event'],
                ],
            ]);

        $this->assertDatabaseHas('registrations', [
            'registration_number' => 'S-0001',
        ]);
    }

    public function test_penomoran_per_kategori_terpisah(): void
    {
        // Siswa SMP #1
        $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ])->assertStatus(201);

        // Siswa SMA #1 → kode S juga → nomor lanjut S-0002
        $this->postJson('/api/registrations', [
            'full_name' => 'Andi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'andi@example.com', 'phone' => '08124',
            'nis' => '222', 'class' => '10A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMA->id,
        ])->assertStatus(201);

        // Umum #1 → kode A → nomor mulai A-0001
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Ibu Sari', 'gender' => 'P',
            'birth_place' => 'Jakarta', 'birth_date' => '1985-03-10',
            'email' => 'sari@example.com', 'phone' => '08125',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryUmum->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.registration_number', 'A-0001');

        $this->assertDatabaseHas('registrations', ['registration_number' => 'S-0001']);
        $this->assertDatabaseHas('registrations', ['registration_number' => 'S-0002']);
        $this->assertDatabaseHas('registrations', ['registration_number' => 'A-0001']);
    }

    public function test_barcode_unik(): void
    {
        $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ]);

        $this->postJson('/api/registrations', [
            'full_name' => 'Andi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'andi@example.com', 'phone' => '08124',
            'nis' => '222', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ]);

        $barcodes = Registration::pluck('barcode')->toArray();
        $this->assertCount(2, array_unique($barcodes));
    }

    public function test_validasi_gagal_jika_field_kosong(): void
    {
        $response = $this->postJson('/api/registrations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'full_name', 'gender', 'birth_place', 'birth_date',
                'email', 'phone', 'event_id', 'category_id',
            ]);
    }

    // ========================================================================
    // VALIDASI FIELD PER KATEGORI
    // ========================================================================

    public function test_siswa_wajib_isi_nis_dan_kelas(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nis', 'class']);
    }

    public function test_guru_wajib_isi_nip_jabatan_unit(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Bu Sari', 'gender' => 'P',
            'birth_place' => 'Jakarta', 'birth_date' => '1985-03-10',
            'email' => 'sari@example.com', 'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nip', 'position', 'unit']);
    }

    public function test_nip_guru_harus_terdaftar_di_data_pegawai(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Orang Luar', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '1990-01-01',
            'email' => 'luar@example.com', 'phone' => '08123',
            'nip' => '99999999', 'position' => 'Guru', 'unit' => 'SMP',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nip'])
            ->assertJsonPath('errors.nip.0', 'NIP tidak terdaftar sebagai guru/karyawan Alcent.');

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_nip_guru_tidak_boleh_daftar_dua_kali_di_event_sama(): void
    {
        $payload = [
            'full_name' => 'Bu Sari', 'gender' => 'P',
            'birth_place' => 'Jakarta', 'birth_date' => '1985-03-10',
            'email' => 'sari@example.com', 'phone' => '08123',
            'nip' => 'NIP001', 'position' => 'Guru', 'unit' => 'SD',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
        ];

        $this->postJson('/api/registrations', $payload)->assertStatus(201);

        $this->postJson('/api/registrations', array_merge($payload, ['email' => 'sari.lain@example.com']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nip']);
    }

    public function test_nama_guru_mengikuti_data_pegawai(): void
    {
        $this->postJson('/api/registrations', [
            'full_name' => 'Nama Ketik Sendiri', 'gender' => 'P',
            'birth_place' => 'Jakarta', 'birth_date' => '1985-03-10',
            'email' => 'sari@example.com', 'phone' => '08123',
            'nip' => 'NIP001', 'position' => 'Guru', 'unit' => 'SD',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
        ])->assertStatus(201);

        $this->assertSame('Bu Sari', Participant::where('email', 'sari@example.com')->sole()->full_name);
    }

    public function test_orangtua_wajib_isi_nama_dan_kelas_anak(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Bapak Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '1980-05-15',
            'email' => 'bapak@example.com', 'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryOrangtua->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['child_name', 'child_class']);
    }

    public function test_umum_tidak_wajib_isi_tahun_lulus(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Umum Test', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'umum@example.com', 'phone' => '08123',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryUmum->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.registration_number', 'A-0001');

        $this->assertNull(
            Participant::where('email', 'umum@example.com')->sole()->graduation_year
        );
    }

    // ========================================================================
    // DUPLIKAT
    // ========================================================================

    public function test_duplikat_email_di_event_sama_ditolak(): void
    {
        $payload = [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ];

        $this->postJson('/api/registrations', $payload)->assertStatus(201);
        $this->postJson('/api/registrations', $payload)->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_email_sama_boleh_daftar_di_event_berbeda(): void
    {
        $event2 = Event::create([
            'name' => 'Centaurian FunRun 2027',
            'event_date' => '2027-05-15',
            'is_active' => false,
        ]);

        $payload = [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'category_id' => $this->categorySiswaSMP->id,
        ];

        $this->postJson('/api/registrations', array_merge($payload, [
            'event_id' => $this->event->id,
        ]))->assertStatus(201);

        $this->postJson('/api/registrations', array_merge($payload, [
            'event_id' => $event2->id,
        ]))->assertStatus(201);

        $this->assertEquals(1, Participant::count());
        $this->assertEquals(2, Registration::count());
    }

    // ========================================================================
    // AUTO-SET PAKET
    // ========================================================================

    public function test_paket_guru_otomatis_gratis(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Bu Sari', 'gender' => 'P',
            'birth_place' => 'Jakarta', 'birth_date' => '1985-03-10',
            'email' => 'sari@example.com', 'phone' => '08123',
            'nip' => 'NIP001', 'position' => 'Guru', 'unit' => 'SD',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryGuru->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.payment_status', 'free')
            ->assertJsonPath('data.registration.registration_status', 'confirmed');
    }

    public function test_paket_siswa_otomatis_100k(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.payment_status', 'unpaid')
            ->assertJsonPath('data.registration.registration_status', 'pending');
    }

    public function test_paket_orangtua_otomatis_150k(): void
    {
        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Bapak Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '1980-05-15',
            'email' => 'bapak@example.com', 'phone' => '08123',
            'child_name' => 'Anak Budi', 'child_class' => '5A',
            'event_id' => $this->event->id,
            'category_id' => $this->categoryOrangtua->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.registration.payment_status', 'unpaid');
    }

    // ========================================================================
    // KUOTA
    // ========================================================================

    public function test_registrasi_ditolak_jika_kuota_penuh(): void
    {
        // Set kuota SMP = 1
        $this->categorySiswaSMP->update(['quota' => 1]);

        // Daftar #1 → berhasil
        $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ])->assertStatus(201);

        // Daftar #2 → ditolak karena kuota penuh
        $this->postJson('/api/registrations', [
            'full_name' => 'Andi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'andi@example.com', 'phone' => '08124',
            'nis' => '222', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ])->assertStatus(422);
    }

    // ========================================================================
    // SETTING
    // ========================================================================

    public function test_registrasi_ditolak_jika_pendaftaran_ditutup(): void
    {
        Setting::create(['key' => 'registration_open', 'value' => '0']);

        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registration']);
    }

    public function test_registrasi_ditolak_jika_melewati_deadline(): void
    {
        Setting::create(['key' => 'registration_open', 'value' => '1']);
        Setting::create(['key' => 'registration_deadline', 'value' => '2020-01-01']);

        $response = $this->postJson('/api/registrations', [
            'full_name' => 'Budi', 'gender' => 'L',
            'birth_place' => 'Bandung', 'birth_date' => '2000-05-15',
            'email' => 'budi@example.com', 'phone' => '08123',
            'nis' => '111', 'class' => '9A',
            'event_id' => $this->event->id,
            'category_id' => $this->categorySiswaSMP->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registration']);
    }
}
