<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_aset',
        'nama_alat',
        'jenis',
        'spesifikasi',
        'kegunaan',
        'stok',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
        ];
    }

    public function assetItems(): HasMany
    {
        return $this->hasMany(AssetItem::class);
    }

    public function readyUnitsCount(): int
    {
        return $this->assetItems()
            ->where('status', 'tersedia')
            ->where('kondisi', 'baik')
            ->count();
    }
}
