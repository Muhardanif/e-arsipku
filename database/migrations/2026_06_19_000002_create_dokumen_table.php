<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_dokumen')->unique();
            $table->string('judul');
            $table->foreignId('kategori_id')->constrained('kategori_dokumen')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_dokumen');
            $table->date('tanggal_berlaku')->nullable();
            $table->date('tanggal_berakhir')->nullable();
            $table->string('pengesah')->nullable();
            $table->enum('status', ['berlaku', 'kadaluarsa', 'dicabut'])->default('berlaku');
            $table->unsignedInteger('versi_terkini')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('kategori_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
