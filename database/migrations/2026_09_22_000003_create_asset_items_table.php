<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_seri_atau_qr')->unique();
            $table->enum('status', ['tersedia', 'dipinjam'])->default('tersedia');
            $table->enum('kondisi', ['baik', 'rusak', 'rusak_total'])->default('baik');
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_items');
    }
};
