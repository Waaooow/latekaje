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

    protected static ?string $navigationLabel = 'Kelola User';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('nis')
                ->label('NIS')
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->placeholder('Khusus akun siswa'),

            Select::make('role')
                ->label('Role')
                ->required()
                ->default('siswa')
                ->live()
                ->options([
                    'superadmin' => 'Superadmin',
                    'toolman' => 'Toolman',
                    'anak_pkl' => 'Anak PKL',
                    'siswa' => 'Siswa',
                ]),

            Toggle::make('is_active')
                ->label('Akun aktif')
                ->default(true)
                ->helperText('Matikan untuk memblokir login tanpa menghapus akun.'),

            CheckboxList::make('permissions.allow')
                ->label('Hak khusus: IZINKAN (di luar role)')
                ->options(\App\Support\Acl::ABILITIES)
                ->columns(2)
                ->visible(fn (): bool => (bool) auth()->user()?->isSuperadmin()),

            CheckboxList::make('permissions.deny')
                ->label('Hak khusus: LARANG (walau role membolehkan)')
                ->options(\App\Support\Acl::ABILITIES)
                ->columns(2)
                ->visible(fn (): bool => (bool) auth()->user()?->isSuperadmin()),

            TextInput::make('password')
                ->label('Password Baru')
                ->password()
                ->revealable()
                ->dehydrated(fn ($state): bool => filled($state))
                ->dehydrateStateUsing(fn ($state): ?string => filled($state) ? Hash::make($state) : null)
                ->required(fn (string $context): bool => $context === 'create')
                ->helperText(fn (string $context): string => $context === 'edit' ? 'Kosongkan bila tidak diganti.' : '')
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('student.nama')
                    ->label('Data Siswa')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'superadmin' => 'Superadmin',
                        'toolman' => 'Toolman',
                        'anak_pkl' => 'Anak PKL',
                        default => 'Siswa',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'toolman' => 'warning',
                        'anak_pkl' => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'superadmin' => 'Superadmin',
                        'toolman' => 'Toolman',
                        'anak_pkl' => 'Anak PKL',
                        'siswa' => 'Siswa',
                    ])
                    ->placeholder('Semua'),
            ])
            ->recordActions([
                Action::make('toggleAktif')
                    ->label(fn ($record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
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
                            ->title($record->is_active ? 'Akun diaktifkan' : 'Akun dinonaktifkan + sesi ditendang')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->label('Hapus'),
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
