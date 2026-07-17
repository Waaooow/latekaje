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
    Schema::create('asset_items', function (Blueprint $table) {
        $table->id();
        // Menghubungkan ke katalog utama (Asset)
        $table->foreignId('asset_id')->constrained()->onDelete('cascade');
        // Kode unik per-barang yang nanti dijadikan QR Code (Contoh: TJKT-MK-001-A)
        $table->string('nomor_seri_atau_qr')->unique();
        $table->enum('status', ['tersedia', 'dipinjam', 'rusak', 'maintenance'])->default('tersedia');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_items');
    }
};
