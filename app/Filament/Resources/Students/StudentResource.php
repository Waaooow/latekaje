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

    public static function getNavigationLabel(): string
    {
        return __('students.model_label');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('students.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('students.model_plural');
    }

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nis')
                ->label(__('students.nis_label'))
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->placeholder(__('students.nis_placeholder')),

            TextInput::make('nama')
                ->label(__('students.full_name_label'))
                ->required()
                ->maxLength(255),

            TextInput::make('kelas')
                ->label(__('students.class_label'))
                ->required()
                ->maxLength(255)
                ->placeholder(__('students.class_placeholder')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nis')
                    ->label(__('students.nis_label'))
                    ->badge()
                    ->copyable()
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('nama')
                    ->label(__('students.name_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas')
                    ->label(__('students.class_label'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                IconColumn::make('aktif')
                    ->label(__('students.active_label'))
                    ->boolean(),

                TextColumn::make('active_loans_count')
                    ->label(__('students.loans_label'))
                    ->counts('activeLoans')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                Action::make('buatAkun')
                    ->label(__('students.create_account'))
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
                            ->title(__('students.account_created_title'))
                            ->body(__('students.account_created_body', ['nis' => $user->nis]))
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
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
