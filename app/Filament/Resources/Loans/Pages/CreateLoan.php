<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Loan;
use App\Services\LoanService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function handleRecordCreation(array $data): Loan
    {
        $user = auth()->user();
        $studentId = isset($data['student_id']) && $data['student_id'] !== '' ? (int) $data['student_id'] : null;

        // Siswa login pribadi: identitas dikunci ke akunnya (anti ketuker).
        if ($user?->isSiswa() && ($user->student_id || $user->nis)) {
            $student = $user->student;
            $studentId = $user->student_id;
            $data['nis'] = $user->nis;
            $data['nama_siswa'] = $student?->nama ?? $user->name;
            $data['kelas'] = $student?->kelas ?? ($data['kelas'] ?? '-');
        }

        $loan = LoanService::borrow(
            (int) $data['asset_item_id'],
            (string) $data['nama_siswa'],
            (string) $data['kelas'],
            (string) ($data['return_pin'] ?? ''),
            $studentId,
            isset($data['nis']) && $data['nis'] !== '' ? (string) $data['nis'] : null,
        );

        Notification::make()
            ->title('Peminjaman tercatat — PIN: '.$loan->return_pin)
            ->body('Catat/foto PIN ini. Untuk mengembalikan: scan QR + PIN + nama pengembali.')
            ->success()
            ->persistent()
            ->send();

        return $loan;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
