<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_items', function (Blueprint $table) {
            // Menambahkan kolom lokasi dengan default di 'gudang'
            $table->string('lokasi')->default('gudang')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('asset_items', function (Blueprint $table) {
            $table->dropColumn('lokasi');
        });
    }
};
