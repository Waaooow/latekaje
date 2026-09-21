<?php

namespace App\Filament\Resources\AssetItems\Pages;

use App\Filament\Resources\AssetItems\AssetItemResource;
use App\Models\Asset;
use App\Services\AssetItemService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAssetItem extends CreateRecord
{
    protected static string $resource = AssetItemResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $serials = collect(preg_split('/\r\n|\r|\n/', (string) ($data['sn_manual'] ?? '')))
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values()
            ->all();

        $typed = trim((string) ($data['nomor_seri_atau_qr'] ?? ''));

        if ($typed !== '' && ! in_array($typed, $serials, true)) {
            array_unshift($serials, $typed);
        }

        $qty = max((int) ($data['jumlah'] ?? 1), count($serials), 1);

        $asset = Asset::findOrFail($data['asset_id']);

        $units = AssetItemService::bulkCreate(
            $asset,
            $qty,
            (int) $data['location_id'],
            (string) ($data['kondisi'] ?? 'baik'),
            (string) ($data['status'] ?? 'tersedia'),
            $serials,
        );

        if ($units->count() > 1) {
            Notification::make()
                ->title($units->count().' unit berhasil dibuat')
                ->body('Kode: '.$units->first()->nomor_seri_atau_qr.' s/d '.$units->last()->nomor_seri_atau_qr)
                ->success()
                ->send();
        }

        return $units->first();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
