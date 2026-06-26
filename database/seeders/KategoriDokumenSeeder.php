<?php

namespace Database\Seeders;

use App\Models\KategoriDokumen;
use Illuminate\Database\Seeder;

class KategoriDokumenSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['kode' => 'SOP',  'nama' => 'Standar Operasional Prosedur', 'format_nomor' => '445/{NO}/SOP/K3/437.52.27/{TAHUN}', 'digit_nomor' => 3, 'periode_review_tahun' => 3],
            ['kode' => 'SK',   'nama' => 'Surat Keputusan', 'periode_review_tahun' => 3],
            ['kode' => 'SM',   'nama' => 'Surat Masuk'],
            ['kode' => 'SK-K', 'nama' => 'Surat Keluar'],
            ['kode' => 'AKR',  'nama' => 'Dokumen Akreditasi', 'periode_review_tahun' => 1],
            ['kode' => 'PER',  'nama' => 'Peraturan Internal', 'periode_review_tahun' => 2],
        ];

        foreach ($kategori as $item) {
            KategoriDokumen::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
