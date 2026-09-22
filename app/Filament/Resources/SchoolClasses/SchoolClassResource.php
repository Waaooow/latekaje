<?php

namespace App\Filament\Resources\SchoolClasses;

use App\Filament\Resources\SchoolClasses\Pages\CreateSchoolClass;
use App\Filament\Resources\SchoolClasses\Pages\EditSchoolClass;
use App\Filament\Resources\SchoolClasses\Pages\ListSchoolClasses;
use App\Models\SchoolClass;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $modelLabel = 'Kelas';

    protected static ?string $pluralModelLabel = 'Kelas';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Kelas';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->label('Kode')
                ->required()
                ->unique(ignoreRecord: true)
                ->alphaDash()
                ->maxLength(64)
                ->placeholder('cth: x_tjkt_1, tamu_eksternal'),

            TextInput::make('label')
                ->label('Nama Tampil')
                ->required()
                ->maxLength(255)
                ->placeholder('cth: X TJKT 1, Tamu / Eksternal'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->label('Kode')
                    ->badge()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('loans_count')
                    ->label('Total Pinjam')
                    ->counts('loans')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchoolClasses::route('/'),
            'create' => CreateSchoolClass::route('/create'),
            'edit' => EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
