<?php

namespace App\Services;

use App\Events\LoanActivityEvent;
use App\Models\AssetItem;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanService
{
    /**
     * @throws ValidationException
     */
    public static function borrow(int $itemId, string $nama, string $kelas, ?string $pin = null, ?int $studentId = null, ?string $nis = null): Loan
    {
        $pin = trim((string) $pin);

        if ($pin === '') {
            $pin = self::newPin();
        } elseif (! preg_match('/^[0-9]{6}$/', $pin)) {
            throw ValidationException::withMessages([
                'return_pin' => __('loans.val_pin_format'),
            ]);
        }

        [$loan, $itemCode] = DB::transaction(function () use ($itemId, $nama, $kelas, $pin, $studentId, $nis) {
            /** @var AssetItem $item */
            $item = AssetItem::lockForUpdate()->findOrFail($itemId);

            if ($item->status === 'dipinjam' || $item->activeLoan()->exists()) {
                throw ValidationException::withMessages([
                    'asset_item_id' => __('loans.val_borrowed'),
                ]);
            }

            if ($item->kondisi !== 'baik') {
                throw ValidationException::withMessages([
                    'asset_item_id' => __('loans.val_damaged'),
                ]);
            }

            $loan = Loan::create([
                'asset_item_id' => $item->id,
                'nama_siswa' => $nama,
                'kelas' => $kelas,
                'status' => 'aktif',
                'return_pin' => $pin,
                'student_id' => $studentId,
                'nis' => $nis,
            ]);

            $item->update(['status' => 'dipinjam']);

            return [$loan, $item->nomor_seri_atau_qr];
        });

        broadcast(new LoanActivityEvent('borrow', $loan->id, $itemCode, $nama, $kelas))->toOthers();

        return $loan;
    }

    /**
     * Payload: qr, pin, returned_by, return_relation (sendiri|wakil),
     * received_by (opsional, nama petugas), return_photo_path (opsional).
     *
     * @throws ValidationException
     */
    public static function returnLoan(Loan $loan, array $payload): void
    {
        $info = DB::transaction(function () use ($loan, $payload) {
            /** @var Loan $lockedLoan */
            $lockedLoan = Loan::lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'qr' => __('loans.val_closed'),
                ]);
            }

            /** @var AssetItem|null $item */
            $item = $lockedLoan->asset_item_id
                ? AssetItem::lockForUpdate()->find($lockedLoan->asset_item_id)
                : null;

            if (! $item) {
                throw ValidationException::withMessages([
                    'qr' => __('loans.val_unit_deleted'),
                ]);
            }

            if (trim($item->nomor_seri_atau_qr) !== trim((string) ($payload['qr'] ?? ''))) {
                throw ValidationException::withMessages([
                    'qr' => __('loans.val_qr_mismatch'),
                ]);
            }

            if ($lockedLoan->return_pin && trim((string) ($payload['pin'] ?? '')) !== $lockedLoan->return_pin) {
                throw ValidationException::withMessages([
                    'pin' => __('loans.val_pin_wrong'),
                ]);
            }

            $returnedBy = trim((string) ($payload['returned_by'] ?? ''));

            if ($returnedBy === '') {
                throw ValidationException::withMessages([
                    'returned_by' => __('loans.val_returner_required'),
                ]);
            }

            $relation = ($payload['return_relation'] ?? 'sendiri') === 'wakil' ? 'wakil' : 'sendiri';
            $receivedBy = trim((string) ($payload['received_by'] ?? ''));

            $lockedLoan->update([
                'status' => 'kembali',
                'tanggal_kembali' => now(),
                'returned_by' => $returnedBy,
                'return_relation' => $relation,
                'return_method' => $receivedBy !== '' ? 'petugas' : 'mandiri',
                'received_by' => $receivedBy !== '' ? $receivedBy : null,
                'return_photo_path' => $payload['return_photo_path'] ?? null,
            ]);

            $item->update(['status' => 'tersedia']);

            return [
                'loan_id' => $lockedLoan->id,
                'item_code' => $item->nomor_seri_atau_qr,
                'borrower' => $lockedLoan->nama_siswa,
                'kelas' => $lockedLoan->kelas,
                'by' => $returnedBy,
            ];
        });

        broadcast(new LoanActivityEvent(
            'return',
            $info['loan_id'],
            $info['item_code'],
            $info['borrower'],
            $info['kelas'],
            $info['by'],
        ))->toOthers();
    }

    private static function newPin(): string
    {
        return sprintf('%06d', random_int(0, 999999));
    }
}
