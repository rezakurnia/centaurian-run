<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'username'   => 'admin',
                'password'   => Hash::make('admin123'),
                'email'      => 'admin@centaurian.test',
                'role'       => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username'   => 'panitia',
                'password'   => Hash::make('panitia123'),
                'email'      => 'panitia@centaurian.test',
                'role'       => 'panitia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}