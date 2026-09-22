<?php

namespace App\Filament\Imports;

use App\Models\Student;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nis')
                ->rules(['nullable', 'string', 'max:64'])
                ->examples(['1001', ''])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('nama')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->examples(['Budi Santoso'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('kelas')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->examples(['X TJKT 1'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('password')
                ->rules(['nullable', 'string', 'max:255'])
                ->examples(['1001'])
                ->fillRecordUsing(fn () => null),
        ];
    }

    public function resolveRecord(): ?Student
    {
        $nama = trim((string) ($this->data['nama'] ?? ''));
        $kelas = trim((string) ($this->data['kelas'] ?? ''));
        $nis = trim((string) ($this->data['nis'] ?? ''));

        if ($nama === '' || $kelas === '') {
            return null;
        }

        if ($nis !== '') {
            $student = Student::firstOrNew(['nis' => $nis]);
            $student->fill(['nama' => $nama, 'kelas' => $kelas, 'aktif' => true]);
            $student->save();

            $password = trim((string) ($this->data['password'] ?? ''));

            User::updateOrCreate(
                ['nis' => $nis, 'role' => 'siswa'],
                [
                    'name' => $nama,
                    'email' => $nis.'@siswa.latekaje',
                    'password' => $password !== '' ? $password : $nis,
                    'student_id' => $student->id,
                ]
            );

            return $student;
        }

        return new Student(['nama' => $nama, 'kelas' => $kelas, 'aktif' => true]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor siswa selesai: '.number_format($import->successful_rows).' baris berhasil';

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ', '.number_format($failed).' baris gagal';
        }

        return $body.'.';
    }
}
