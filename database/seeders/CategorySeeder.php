<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Siswa',         'code' => 'SW', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Guru Karyawan', 'code' => 'GK', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alumni',        'code' => 'AL', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Orangtua',      'code' => 'OT', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'External',      'code' => 'EX', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}