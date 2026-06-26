<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_dokumen', function (Blueprint $table) {
            // Periode review berkala dalam tahun. NULL = kategori ini tidak perlu ditinjau berkala.
            $table->unsignedTinyInteger('periode_review_tahun')->nullable()->after('digit_nomor');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_dokumen', function (Blueprint $table) {
            $table->dropColumn('periode_review_tahun');
        });
    }
};
