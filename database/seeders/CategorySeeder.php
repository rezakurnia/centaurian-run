<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name'       => 'Siswa SMP',
                'code'       => 'S',
                'quota'      => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Siswa SMA',
                'code'       => 'S',
                'quota'      => 150,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Guru Karyawan',
                'code'       => 'E',
                'quota'      => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Orangtua',
                'code'       => 'P',
                'quota'      => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Alumni',
                'code'       => 'A',
                'quota'      => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}