<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_akses', function (Blueprint $table) {
            $table->id();
            $table->string('role', 20);
            $table->string('menu', 40);
            $table->timestamps();

            $table->unique(['role', 'menu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_akses');
    }
};
