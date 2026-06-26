<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_review', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            $table->date('tanggal_review');
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('hasil')->default('sesuai'); // sesuai | perlu_revisi
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['dokumen_id', 'tanggal_review']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_review');
    }
};
