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
        $loan = LoanService::borrow(
            (int) $data['asset_item_id'],
            (string) $data['nama_siswa'],
            (string) $data['kelas'],
            (string) ($data['return_pin'] ?? ''),
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
