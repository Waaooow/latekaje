<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    // 🟢 PERBAIKAN: Masukkan 'kegunaan' ke dalam list izin mass assignment
    protected $fillable = [
        'nama_alat',
        'kode_aset',
        'spesifikasi',
        'jenis',
        'status',
        'stok',
        'kegunaan', 
    ];

    public function assetItems(): HasMany
    {
        return $this->hasMany(AssetItem::class);
    }
}