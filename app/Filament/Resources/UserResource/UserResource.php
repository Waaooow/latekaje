<?php

namespace App\Filament\Resources\UserResource;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function getNavigationLabel(): string
    {
        return __('common.nav_users');
    }

    public static function getModelLabel(): string
    {
        return __('users.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('users.model_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('users.name_label'))
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label(__('users.email_label'))
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('code')
                ->label(__('users.code_label'))
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->placeholder(__('users.code_placeholder')),

            Select::make('role')
                ->label(__('users.role_label'))
                ->required()
                ->default('users')
                ->live()
                ->options([
                    'superadmin' => __('users.role_superadmin'),
                    'admin' => __('users.role_admin'),
                    'staff' => __('users.role_staff'),
                    'assistant' => __('users.role_assistant'),
                    'users' => __('users.role_users'),
                ]),

            Toggle::make('is_active')
                ->label(__('users.account_active_label'))
                ->default(true)
                ->helperText(__('users.account_active_helper')),

            CheckboxList::make('permissions.allow')
                ->label(__('users.perm_allow_label'))
                ->options(\App\Support\Acl::labels())
                ->columns(2)
                ->visible(fn (): bool => (bool) auth()->user()?->isSuperadmin()),

            CheckboxList::make('permissions.deny')
                ->label(__('users.perm_deny_label'))
                ->options(\App\Support\Acl::labels())
                ->columns(2)
                ->visible(fn (): bool => (bool) auth()->user()?->isSuperadmin()),

            TextInput::make('password')
                ->label(__('users.new_password_label'))
                ->password()
                ->revealable()
                ->dehydrated(fn ($state): bool => filled($state))
                ->dehydrateStateUsing(fn ($state): ?string => filled($state) ? Hash::make($state) : null)
                ->required(fn (string $context): bool => $context === 'create')
                ->helperText(fn (string $context): string => $context === 'edit' ? __('users.password_helper_edit') : '')
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('users.name_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('users.email_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label(__('users.code_label'))
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('member.name')
                    ->label(__('users.member_data_label'))
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('role')
                    ->label(__('users.role_label'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'superadmin' => __('users.role_superadmin'),
                        'admin' => __('users.role_admin'),
                        'staff' => __('users.role_staff'),
                        'assistant' => __('users.role_assistant'),
                        default => __('users.role_users'),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'admin' => 'warning',
                        'staff' => 'info',
                        'assistant' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label(__('common.active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('users.created_label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('users.role_label'))
                    ->options([
                        'superadmin' => __('users.role_superadmin'),
                        'admin' => __('users.role_admin'),
                        'staff' => __('users.role_staff'),
                        'assistant' => __('users.role_assistant'),
                        'users' => __('users.role_users'),
                    ])
                    ->placeholder(__('common.all')),
            ])
            ->recordActions([
                Action::make('toggleAktif')
                    ->label(fn ($record): string => $record->is_active ? __('users.deactivate') : __('users.activate'))
                    ->icon(fn ($record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn ($record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(fn (): bool => auth()->user()?->isSuperadmin())
                    ->action(function ($record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        if (! $record->is_active) {
                            \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $record->id)->delete();
                        }

                        Notification::make()
                            ->title($record->is_active ? __('users.activated_title') : __('users.deactivated_title'))
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label(__('common.edit')),
                DeleteAction::make()
                    ->label(__('common.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->label(__('common.delete')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
