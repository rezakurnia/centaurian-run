<?php

namespace Tests\Feature;

use App\Models\Employee;
use Tests\TestCase;

class EmployeeLookupTest extends TestCase
{
    public function test_nip_terdaftar_mengembalikan_nama_pegawai(): void
    {
        Employee::create(['nip' => '14000012', 'name' => 'Miqdad Nasrul Haq']);

        $this->getJson('/api/employees/14000012')
            ->assertOk()
            ->assertJsonPath('data.nip', '14000012')
            ->assertJsonPath('data.name', 'Miqdad Nasrul Haq');
    }

    public function test_nip_tidak_terdaftar_mengembalikan_404(): void
    {
        Employee::create(['nip' => '14000012', 'name' => 'Miqdad Nasrul Haq']);

        $this->getJson('/api/employees/99999999')
            ->assertNotFound()
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'NIP tidak terdaftar sebagai guru/karyawan Alcent.');
    }

    public function test_nip_dengan_spasi_di_tepi_tetap_ditemukan(): void
    {
        Employee::create(['nip' => '14000012', 'name' => 'Miqdad Nasrul Haq']);

        $this->getJson('/api/employees/'.rawurlencode(' 14000012 '))
            ->assertOk()
            ->assertJsonPath('data.name', 'Miqdad Nasrul Haq');
    }
}
