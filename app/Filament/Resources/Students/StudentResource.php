<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Models\Student;
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

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationLabel = 'Siswa';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $pluralModelLabel = 'Siswa';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nis')
                ->label('NIS')
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->placeholder('cth: 1001 (boleh kosong)'),

            TextInput::make('nama')
                ->label('Nama Lengkap')
                ->required()
                ->maxLength(255),

            TextInput::make('kelas')
                ->label('Kelas')
                ->required()
                ->maxLength(255)
                ->placeholder('cth: X TJKT 1'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->badge()
                    ->copyable()
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('loans_count')
                    ->label('Pinjam')
                    ->counts('loans')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                Action::make('buatAkun')
                    ->label('Buat Akun')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->visible(fn ($record): bool => filled($record->nis) && ! User::where('nis', $record->nis)->where('role', 'siswa')->exists())
                    ->action(function ($record): void {
                        $user = User::updateOrCreate(
                            ['nis' => $record->nis, 'role' => 'siswa'],
                            [
                                'name' => $record->nama,
                                'email' => $record->nis.'@siswa.latekaje',
                                'password' => $record->nis,
                                'student_id' => $record->id,
                            ]
                        );

                        Notification::make()
                            ->title('Akun login dibuat')
                            ->body('NIS: '.$user->nis.' | Password awal: NIS-nya. Minta siswa ganti di Profil.')
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                EditAction::make()->label('Ubah'),
                DeleteAction::make()->label('Hapus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
