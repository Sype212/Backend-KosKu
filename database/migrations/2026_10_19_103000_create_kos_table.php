<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->text('alamat');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_per_bulan', 14, 2);
            $table->unsignedInteger('jumlah_kamar');
            $table->unsignedInteger('kamar_tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kos');
    }
};