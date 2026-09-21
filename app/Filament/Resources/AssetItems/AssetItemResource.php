<?php

namespace App\Filament\Resources\AssetItems;

use App\Filament\Resources\AssetItems\Pages\CreateAssetItem;
use App\Filament\Resources\AssetItems\Pages\EditAssetItem;
use App\Filament\Resources\AssetItems\Pages\ListAssetItems;
use App\Filament\Resources\AssetItems\Schemas\AssetItemForm;
use App\Filament\Resources\AssetItems\Tables\AssetItemsTable;
use App\Models\AssetItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssetItemResource extends Resource
{
    protected static ?string $model = AssetItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'Data Unit/QR';

    protected static ?string $modelLabel = 'Data Items';

    public static function form(Schema $schema): Schema
    {
        return AssetItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetItems::route('/'),
            'create' => CreateAssetItem::route('/create'),
            'edit' => EditAssetItem::route('/{record}/edit'),
        ];
    }
}
