<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_dokumen', function (Blueprint $table) {
            // Template nomor dokumen per kategori, mis. "445/{NO}/SOP/K3/437.52.27/{TAHUN}".
            // Null = penomoran hanya manual untuk kategori ini.
            $table->string('format_nomor')->nullable()->after('nama');
            // Jumlah digit (zero-pad) untuk token {NO}.
            $table->unsignedTinyInteger('digit_nomor')->default(3)->after('format_nomor');
        });

        Schema::table('dokumen', function (Blueprint $table) {
            // Nomor urut yang dipakai saat penomoran otomatis (null bila diisi manual).
            $table->unsignedInteger('nomor_urut')->nullable()->after('nomor_dokumen');
            // Tahun acuan increment — increment reset per tahun.
            $table->unsignedSmallInteger('tahun_nomor')->nullable()->after('nomor_urut');

            $table->index(['kategori_id', 'tahun_nomor']);
        });
    }

    public function down(): void
    {
        Schema::table('dokumen', function (Blueprint $table) {
            $table->dropIndex(['kategori_id', 'tahun_nomor']);
            $table->dropColumn(['nomor_urut', 'tahun_nomor']);
        });

        Schema::table('kategori_dokumen', function (Blueprint $table) {
            $table->dropColumn(['format_nomor', 'digit_nomor']);
        });
    }
};
