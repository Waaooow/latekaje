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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    public static function getModelLabel(): string
    {
        return __('classes.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('classes.model_plural');
    }

    protected static ?string $recordTitleAttribute = 'label';

    public static function getNavigationLabel(): string
    {
        return __('classes.model_label');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->label(__('classes.code_label'))
                ->required()
                ->unique(ignoreRecord: true)
                ->alphaDash()
                ->maxLength(64)
                ->placeholder(__('classes.code_placeholder')),

            TextInput::make('label')
                ->label(__('classes.display_name_label'))
                ->required()
                ->maxLength(255)
                ->placeholder(__('classes.display_name_placeholder')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'activeLoans as active_borrowers' => fn (Builder $q) => $q
                    ->select(DB::raw('COUNT(DISTINCT COALESCE(nis, CONCAT(\'__nama__\', nama_siswa)))')),
            ]))
            ->columns([
                TextColumn::make('label')
                    ->label(__('classes.class_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->label(__('classes.code_column'))
                    ->badge()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('active_borrowers')
                    ->label(__('classes.active_borrowers_label'))
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('common.edit')),
                DeleteAction::make()
                    ->label(__('common.delete')),
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
