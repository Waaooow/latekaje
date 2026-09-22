<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'datetime',
            'tanggal_kembali' => 'datetime',
        ];
    }

    public function assetItem(): BelongsTo
    {
        return $this->belongsTo(AssetItem::class);
    }

    public function getNamaAlatAttribute(): string
    {
        return $this->assetItem?->asset?->nama_alat ?? '(unit dihapus)';
    }

    public function getLamaHariAttribute(): string
    {
        $end = $this->tanggal_kembali ?? now();
        $days = $this->tanggal_pinjam ? (int) $end->diffInDays($this->tanggal_pinjam) : 0;

        if ($this->status === 'aktif' && $days === 0) {
            return 'hari ini';
        }

        return $days.' hari';
    }

    protected static function booted(): void
    {
        static::created(function (Loan $loan): void {
            if ($loan->status === 'aktif') {
                $loan->assetItem()->update(['status' => 'dipinjam']);
            }
        });
    }
}
