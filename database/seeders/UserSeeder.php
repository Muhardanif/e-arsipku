<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Administrator',
                'password' => 'admin123',
                'role' => 'admin',
                'jabatan' => 'Kepala Tata Usaha',
                'aktif' => true,
            ]
        );

        // Akun read-only untuk pimpinan — masuk ke Portal Pencarian (bukan panel admin).
        User::updateOrCreate(
            ['username' => 'kepala'],
            [
                'nama' => 'Kepala Puskesmas',
                'password' => 'kepala123',
                'role' => 'staf',
                'jabatan' => 'Kepala Puskesmas',
                'aktif' => true,
            ]
        );
    }
}
