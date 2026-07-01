<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda bahwa pengguna wajib mengganti kata sandi saat login berikutnya.
     * Diset true ketika admin membuat akun baru atau mereset kata sandi.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('harus_ganti_password')->default(false)->after('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('harus_ganti_password');
        });
    }
};
