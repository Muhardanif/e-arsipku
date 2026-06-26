<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klaster', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();   // mis. K1..K5 — dipakai token {KLASTER}
            $table->string('nama');                  // mis. Klaster Ibu dan Anak
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::table('dokumen', function (Blueprint $table) {
            // Klaster acuan saat penomoran otomatis (null bila kategori tak memakai klaster / manual).
            $table->foreignId('klaster_id')->nullable()->after('kategori_id')
                ->constrained('klaster')->nullOnDelete();
            // Increment SOP berjalan per klaster + tahun.
            $table->index(['kategori_id', 'klaster_id', 'tahun_nomor'], 'dokumen_nomor_bucket_index');
        });
    }

    public function down(): void
    {
        Schema::table('dokumen', function (Blueprint $table) {
            $table->dropIndex('dokumen_nomor_bucket_index');
            $table->dropConstrainedForeignId('klaster_id');
        });

        Schema::dropIfExists('klaster');
    }
};
