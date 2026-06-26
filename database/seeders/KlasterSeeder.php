<?php

namespace Database\Seeders;

use App\Models\Klaster;
use Illuminate\Database\Seeder;

class KlasterSeeder extends Seeder
{
    public function run(): void
    {
        $klaster = [
            ['kode' => 'K1', 'nama' => 'Klaster Manajemen', 'urutan' => 1],
            ['kode' => 'K2', 'nama' => 'Klaster Ibu dan Anak', 'urutan' => 2],
            ['kode' => 'K3', 'nama' => 'Klaster Usia Dewasa dan Lanjut Usia', 'urutan' => 3],
            ['kode' => 'K4', 'nama' => 'Klaster Penanggulangan Penyakit Menular', 'urutan' => 4],
            ['kode' => 'K5', 'nama' => 'Lintas Klaster', 'urutan' => 5],
        ];

        foreach ($klaster as $item) {
            Klaster::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
