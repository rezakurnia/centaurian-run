<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('packages')->insert([
            [
                'name'        => 'Gratis',
                'price'       => 0,
                'description' => 'Hanya untuk Guru Karyawan dan siswa yang ditunjuk. Mendapatkan BIB, Racepack, dan Konsumsi (tanpa medali dan kaos).',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Paket 100K',
                'price'       => 100000,
                'description' => 'Hanya untuk Siswa Alfa Centauri (SMP & SMA). Mendapatkan BIB, Racepack, Konsumsi, dan Medali (tanpa kaos).',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Paket 150K',
                'price'       => 150000,
                'description' => 'Untuk Orangtua dan Alumni. Mendapatkan BIB, Racepack, Konsumsi, Medali, dan Kaos. Termasuk alokasi donasi Palestina dan biaya pendaftaran.',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
