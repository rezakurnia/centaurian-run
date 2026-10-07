<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('packages')->insert([
            ['name' => 'Gratis',      'price' => 0,      'description' => 'Siswa tertentu, Guru & Karyawan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paket 100K',  'price' => 100000, 'description' => 'Siswa',                          'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paket 150K',  'price' => 150000, 'description' => 'Orangtua, Alumni & External',     'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
