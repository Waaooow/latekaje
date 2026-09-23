<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetItem;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetItemService
{
    /**
     * Buat N unit sekaligus untuk satu aset.
     * $serials: daftar SN manual (urutan = unit ke-1,2,...). Unit tanpa SN digenerate otomatis.
     */
    public static function bulkCreate(
        Asset $asset,
        int $qty,
        int $locationId,
        string $kondisi = 'baik',
        string $status = 'tersedia',
        array $serials = [],
    ): Collection {
        $qty = max(1, $qty);
        $serials = array_values(array_filter(array_map('trim', $serials)));

        if (count($serials) > $qty) {
            throw ValidationException::withMessages([
                'sn_manual' => __('units.sn_manual_too_many', ['count' => count($serials), 'qty' => $qty]),
            ]);
        }

        return DB::transaction(function () use ($asset, $qty, $locationId, $kondisi, $status, $serials) {
            $units = collect();

            for ($i = 0; $i < $qty; $i++) {
                $manual = $serials[$i] ?? null;
                $units->push(self::createOne($asset->getKey(), $locationId, $kondisi, $status, $manual));
            }

            return $units;
        });
    }

    private static function createOne(
        int $assetId,
        int $locationId,
        string $kondisi,
        string $status,
        ?string $manualSerial,
    ): AssetItem {
        $tries = 0;

        while (true) {
            $code = ($manualSerial && $tries === 0) ? $manualSerial : AssetItemCode::next();

            try {
                return AssetItem::create([
                    'asset_id' => $assetId,
                    'nomor_seri_atau_qr' => $code,
                    'kondisi' => $kondisi,
                    'location_id' => $locationId,
                    'status' => $status,
                ]);
            } catch (UniqueConstraintViolationException | QueryException $e) {
                if (! self::isDuplicate($e)) {
                    throw $e;
                }

                if ($manualSerial && $tries === 0) {
                    throw ValidationException::withMessages([
                        'sn_manual' => __('units.sn_duplicate', ['serial' => $manualSerial]),
                    ]);
                }

                if (++$tries > 10) {
                    throw $e;
                }
            }
        }
    }

    private static function isDuplicate(QueryException $e): bool
    {
        return $e instanceof UniqueConstraintViolationException
            || str_contains($e->getMessage(), 'Duplicate entry');
    }
}
