<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('loans', function (Blueprint $table) {
        $table->id();
        // Menghubungkan langsung ke UNIT FISIK yang discan, bukan katalognya
        $table->foreignId('asset_item_id')->constrained()->onDelete('cascade');
        $table->string('nama_siswa');
        $table->string('kelas');
        $table->timestamp('tanggal_pinjam')->useCurrent();
        $table->timestamp('tanggal_kembali')->nullable();
        $table->enum('status', ['aktif', 'kembali'])->default('aktif');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
