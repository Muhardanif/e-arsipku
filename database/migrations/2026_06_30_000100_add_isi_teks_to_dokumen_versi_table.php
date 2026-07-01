<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_versi', function (Blueprint $table) {
            // Isi teks hasil ekstraksi (lapisan teks PDF atau OCR) untuk
            // pencarian penuh terhadap konten dokumen, bukan sekadar judul/nomor.
            $table->longText('isi_teks')->nullable()->after('ukuran_file');

            // Metode ekstraksi: teks | ocr | perlu_ocr | tidak_didukung | gagal.
            // 'perlu_ocr' menandai berkas scan yang belum bisa diproses (mis.
            // Tesseract/Ghostscript belum terpasang) agar bisa diindeks ulang nanti.
            $table->string('metode_ekstraksi', 20)->nullable()->after('isi_teks');

            // Kapan berkas terakhir diindeks. NULL = belum pernah.
            $table->timestamp('teks_diindeks_pada')->nullable()->after('metode_ekstraksi');
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_versi', function (Blueprint $table) {
            $table->dropColumn(['isi_teks', 'metode_ekstraksi', 'teks_diindeks_pada']);
        });
    }
};
