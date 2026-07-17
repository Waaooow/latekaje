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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->unique(); // Contoh: TJKT-NET-001
            $table->string('nama_alat');          // Contoh: Router Mikrotik RB951

            // 🟢 REVISI: Diubah jadi string biasa untuk kategori alat (bukan pilihan praktik lagi)
            $table->string('jenis');              // Contoh: Networking, PC, Tools

            // 🟢 REVISI: Dibuat string agar pas dengan TextInput di form
            $table->string('spesifikasi');        // Contoh: RB450G, RAM 256MB

            // 🟢 BARU: Kolom untuk memisahkan fungsi penggunaan alat
            $table->string('kegunaan')->default('praktik'); // Nilai isi: 'praktik' atau 'non_praktik'

            $table->integer('stok')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
