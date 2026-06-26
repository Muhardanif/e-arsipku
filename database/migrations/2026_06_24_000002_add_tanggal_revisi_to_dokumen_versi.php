<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tanggal revisi diisi oleh petugas (kapan revisi dokumen benar-benar berlaku),
 * terpisah dari created_at (kapan baris diunggah ke sistem).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_versi', function (Blueprint $table) {
            $table->date('tanggal_revisi')->nullable()->after('catatan_revisi');
        });

        // Backfill data lama: pakai tanggal unggah sebagai tanggal revisi awal.
        DB::statement('UPDATE dokumen_versi SET tanggal_revisi = DATE(created_at) WHERE tanggal_revisi IS NULL');
    }

    public function down(): void
    {
        Schema::table('dokumen_versi', function (Blueprint $table) {
            $table->dropColumn('tanggal_revisi');
        });
    }
};
