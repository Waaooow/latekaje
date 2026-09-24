<?php

namespace App\Filament\Resources\Groups;

use App\Filament\Resources\Groups\Pages\CreateGroup;
use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Models\Group;
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

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    public static function getModelLabel(): string
    {
        return __('groups.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('groups.model_plural');
    }

    protected static ?string $recordTitleAttribute = 'label';

    public static function getNavigationLabel(): string
    {
        return __('groups.model_label');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 13;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('common.nav_group_master');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->label(__('groups.code_label'))
                ->required()
                ->unique(ignoreRecord: true)
                ->alphaDash()
                ->maxLength(64)
                ->placeholder(__('groups.code_placeholder')),

            TextInput::make('label')
                ->label(__('groups.display_name_label'))
                ->required()
                ->maxLength(255)
                ->placeholder(__('groups.display_name_placeholder')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'activeLoans as active_borrowers' => fn (Builder $q) => $q
                    ->select(DB::raw('COUNT(DISTINCT COALESCE(code, CONCAT(\'__name__\', borrower_name)))')),
            ]))
            ->columns([
                TextColumn::make('label')
                    ->label(__('groups.group_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->label(__('groups.code_column'))
                    ->badge()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('active_borrowers')
                    ->label(__('groups.active_borrowers_label'))
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
            'index' => ListGroups::route('/'),
            'create' => CreateGroup::route('/create'),
            'edit' => EditGroup::route('/{record}/edit'),
        ];
    }
}
