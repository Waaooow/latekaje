<?php

namespace App\Filament\Resources\Members;

use App\Filament\Resources\Members\Pages\CreateMember;
use App\Filament\Resources\Members\Pages\EditMember;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Models\Member;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    public static function getNavigationLabel(): string
    {
        return __('members.model_label');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('members.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('members.model_plural');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label(__('members.code_label'))
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->placeholder(__('members.code_placeholder')),

            TextInput::make('name')
                ->label(__('members.full_name_label'))
                ->required()
                ->maxLength(255),

            TextInput::make('group')
                ->label(__('members.group_label'))
                ->required()
                ->maxLength(255)
                ->placeholder(__('members.group_placeholder')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('members.code_label'))
                    ->badge()
                    ->copyable()
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('name')
                    ->label(__('members.name_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group')
                    ->label(__('members.group_label'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                IconColumn::make('aktif')
                    ->label(__('members.active_label'))
                    ->boolean(),

                TextColumn::make('active_loans_count')
                    ->label(__('members.loans_label'))
                    ->counts('activeLoans')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                Action::make('buatAkun')
                    ->label(__('members.create_account'))
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->visible(fn ($record): bool => filled($record->code) && ! User::where('code', $record->code)->where('role', 'users')->exists())
                    ->action(function ($record): void {
                        $user = User::updateOrCreate(
                            ['code' => $record->code, 'role' => 'users'],
                            [
                                'name' => $record->name,
                                'email' => $record->code.'@member.latekaje',
                                'password' => $record->code,
                                'member_id' => $record->id,
                            ]
                        );

                        Notification::make()
                            ->title(__('members.account_created_title'))
                            ->body(__('members.account_created_body', ['code' => $user->code]))
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                EditAction::make()->label(__('common.edit')),
                DeleteAction::make()->label(__('common.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
