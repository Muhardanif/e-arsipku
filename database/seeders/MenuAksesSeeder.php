<?php

namespace Database\Seeders;

use App\Models\MenuAkses;
use Illuminate\Database\Seeder;

class MenuAksesSeeder extends Seeder
{
    /**
     * Hak akses menu bawaan — meniru perilaku lama:
     * petugas mengelola dokumen, peminjaman, dan laporan; staf hanya lihat.
     */
    public function run(): void
    {
        $bawaan = [
            'petugas' => ['dokumen-kelola', 'peminjaman', 'laporan'],
            'staf' => [],
        ];

        foreach ($bawaan as $role => $menus) {
            foreach ($menus as $menu) {
                MenuAkses::firstOrCreate(['role' => $role, 'menu' => $menu]);
            }
        }
    }
}
