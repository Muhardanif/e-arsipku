<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen', function (Blueprint $table) {
            // Titik mulai siklus review berikutnya. Di-set saat dokumen direvisi
            // atau ditandai sudah direview. NULL → siklus dihitung dari tanggal_dokumen.
            $table->date('tanggal_review_terakhir')->nullable()->after('versi_terkini');
        });
    }

    public function down(): void
    {
        Schema::table('dokumen', function (Blueprint $table) {
            $table->dropColumn('tanggal_review_terakhir');
        });
    }
};
