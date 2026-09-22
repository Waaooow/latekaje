<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_item_id')->nullable()->constrained('asset_items')->restrictOnDelete();
            $table->string('nama_siswa');
            $table->string('kelas');
            $table->timestamp('tanggal_pinjam')->useCurrent();
            $table->timestamp('tanggal_kembali')->nullable();
            $table->enum('status', ['aktif', 'kembali'])->default('aktif');
            $table->unsignedBigInteger('active_item_id')->nullable()->storedAs('CASE WHEN status = \'aktif\' THEN asset_item_id ELSE NULL END')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
