<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_versi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            $table->unsignedInteger('nomor_versi');
            $table->text('catatan_revisi')->nullable();
            $table->string('file_path');
            $table->unsignedBigInteger('ukuran_file')->nullable();
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['dokumen_id', 'nomor_versi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_versi');
    }
};
