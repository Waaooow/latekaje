<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $guarded = [];

    /**
     * Otomatisasi Eloquent: Saat peminjaman baru dibuat,
     * ubah status unit barang tersebut menjadi 'dipinjam'
     */
    protected static function booted()
    {
        static::created(function ($loan) {
            if ($loan->assetItem) {
                $loan->assetItem->update(['status' => 'dipinjam']);
            }
        });
    }

    public function assetItem(): BelongsTo
    {
        return $this->belongsTo(AssetItem::class);
    }
}
