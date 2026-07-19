<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetItem extends Model
{
    // Mengizinkan semua kolom masuk ke database tanpa proteksi berlebih
    protected $guarded = [];

    /**
     * Relasi balik ke katalog utama (Setiap unit memiliki 1 tipe data katalog)
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Relasi ke riwayat peminjaman (Satu unit barang bisa dipinjam berkali-kali secara bergantian)
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
    * Mengambil data peminjaman yang statusnya masih aktif (belum dikembalikan)
    */
    public function activeLoan(): HasOne
    {
        // Relasi ke model Loan, mencari yang statusnya 'aktif'
        return $this->hasOne(Loan::class)->where('status', 'aktif');
    }

    /**
     * ⚡ OTOMATISASI HITUNG STOK (SINKRONISASI REAL-TIME)
     * Berjalan otomatis menghitung ulang jumlah item ketika ada unit yang ditambah, diedit, atau dihapus
     */
    protected static function booted(): void
    {
        // Trigger saat data item ditambah atau diubah
        static::saved(function ($assetItem) {
            if ($assetItem->asset) {
                $assetItem->asset->update([
                    'stok' => $assetItem->asset->assetItems()->count()
                ]);
            }
        });

        // Trigger saat data item dihapus dari lab
        static::deleted(function ($assetItem) {
            if ($assetItem->asset) {
                $assetItem->asset->update([
                    'stok' => $assetItem->asset->assetItems()->count()
                ]);
            }
        });
    }
}