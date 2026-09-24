<?php

namespace App\Filament\Imports;

use App\Models\Member;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class MemberImporter extends Importer
{
    protected static ?string $model = Member::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label(__('members.import_col_code'))
                ->rules(['nullable', 'string', 'max:64'])
                ->examples(['1001', ''])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('name')
                ->label(__('members.import_col_name'))
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->examples(['Budi Santoso'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('group')
                ->label(__('members.import_col_group'))
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->examples(['X TJKT 1'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('password')
                ->label(__('members.import_col_password'))
                ->rules(['nullable', 'string', 'max:255'])
                ->examples(['1001'])
                ->fillRecordUsing(fn () => null),
        ];
    }

    public function resolveRecord(): ?Member
    {
        $name = trim((string) ($this->data['name'] ?? ''));
        $group = trim((string) ($this->data['group'] ?? ''));
        $code = trim((string) ($this->data['code'] ?? ''));

        if ($name === '' || $group === '') {
            return null;
        }

        if ($code !== '') {
            $member = Member::firstOrNew(['code' => $code]);
            $member->fill(['name' => $name, 'group' => $group, 'aktif' => true]);
            $member->save();

            $password = trim((string) ($this->data['password'] ?? ''));

            User::updateOrCreate(
                ['code' => $code, 'role' => 'users'],
                [
                    'name' => $name,
                    'email' => $code.'@member.latekaje',
                    'password' => $password !== '' ? $password : $code,
                    'member_id' => $member->id,
                ]
            );

            return $member;
        }

        return new Member(['name' => $name, 'group' => $group, 'aktif' => true]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('members.import_completed', ['success' => number_format($import->successful_rows)]);

        if ($failed = $import->getFailedRowsCount()) {
            $body .= __('members.import_failed_suffix', ['failed' => number_format($failed)]);
        }

        return $body.'.';
    }
}
