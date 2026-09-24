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
        $memberId = isset($data['member_id']) && $data['member_id'] !== '' ? (int) $data['member_id'] : null;

        // Konsistensi: bila anggota dipilih, snapshot identitas diambil dari master.
        if ($memberId && ($member = \App\Models\Member::find($memberId))) {
            $data['code'] = $member->code;
            $data['borrower_name'] = $member->name;
            $data['group'] = $member->group;
        }

        $loan = LoanService::borrow(
            (int) $data['asset_item_id'],
            (string) $data['borrower_name'],
            (string) $data['group'],
            (string) ($data['return_pin'] ?? ''),
            $memberId,
            isset($data['code']) && $data['code'] !== '' ? (string) $data['code'] : null,
        );

        Notification::make()
            ->title(__('loans.created_title', ['pin' => $loan->return_pin]))
            ->body(__('loans.created_body'))
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
