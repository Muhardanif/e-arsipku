<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah status "draf" untuk dokumen yang sudah dipesan nomornya
        // tetapi berkasnya belum diunggah (menunggu tanda tangan atasan).
        DB::statement("ALTER TABLE dokumen MODIFY status ENUM('draf','berlaku','kadaluarsa','dicabut') NOT NULL DEFAULT 'berlaku'");
    }

    public function down(): void
    {
        // Naikkan dulu draf yang tersisa agar tidak menjadi nilai ilegal.
        DB::table('dokumen')->where('status', 'draf')->update(['status' => 'berlaku']);

        DB::statement("ALTER TABLE dokumen MODIFY status ENUM('berlaku','kadaluarsa','dicabut') NOT NULL DEFAULT 'berlaku'");
    }
};
