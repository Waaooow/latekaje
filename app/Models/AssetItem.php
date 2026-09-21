<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoan(): HasOne
    {
        return $this->hasOne(Loan::class)->where('status', 'aktif');
    }

    protected static function booted(): void
    {
        static::saving(function (AssetItem $item): void {
            if (in_array($item->kondisi, ['rusak', 'rusak_total'], true)) {
                $gudang = Location::where('key', 'gudang')->first();
                if ($gudang) {
                    $item->location_id = $gudang->id;
                }
            }
        });
    }
}
