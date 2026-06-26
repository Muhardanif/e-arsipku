<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda notifikasi yang sudah dibaca/di-dismiss per pengguna.
 *
 * Notifikasi TIDAK disimpan permanen — dihitung on-the-fly dari tabel dokumen
 * tiap halaman dibuka. Tabel ini hanya mencatat notifikasi mana yang sudah
 * ditandai dibaca oleh user, agar tidak muncul lagi.
 *
 * Identitas sebuah notifikasi = (user_id + dokumen_id + jenis + tanggal_acuan).
 * Kolom `tingkat` menyimpan ambang saat ditandai sehingga ketika urgensi
 * meningkat (mis. H-14 → H-7) notifikasi dapat muncul kembali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_dibaca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            $table->string('jenis');          // kedaluwarsa | review
            $table->date('tanggal_acuan');    // tanggal jatuh tempo/kedaluwarsa yang jadi acuan
            $table->string('tingkat')->default('info'); // info|warning|kritis|danger saat ditandai
            $table->timestamp('dibaca_pada')->nullable();

            $table->unique(
                ['user_id', 'dokumen_id', 'jenis', 'tanggal_acuan'],
                'notifikasi_dibaca_unik'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_dibaca');
    }
};
