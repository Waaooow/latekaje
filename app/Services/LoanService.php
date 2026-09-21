<?php

namespace App\Services;

use App\Models\AssetItem;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanService
{
    /**
     * @throws ValidationException
     */
    public static function borrow(int $itemId, string $nama, string $kelas, ?string $pin = null): Loan
    {
        $pin = trim((string) $pin);

        if ($pin === '') {
            $pin = self::newPin();
        } elseif (! preg_match('/^[0-9]{6}$/', $pin)) {
            throw ValidationException::withMessages([
                'return_pin' => 'PIN harus 6 digit angka.',
            ]);
        }

        return DB::transaction(function () use ($itemId, $nama, $kelas, $pin) {
            /** @var AssetItem $item */
            $item = AssetItem::lockForUpdate()->findOrFail($itemId);

            if ($item->status === 'dipinjam' || $item->activeLoan()->exists()) {
                throw ValidationException::withMessages([
                    'asset_item_id' => 'Alat sedang dipinjam.',
                ]);
            }

            if ($item->kondisi !== 'baik') {
                throw ValidationException::withMessages([
                    'asset_item_id' => 'Kondisi tidak layak (rusak).',
                ]);
            }

            $loan = Loan::create([
                'asset_item_id' => $item->id,
                'nama_siswa' => $nama,
                'kelas' => $kelas,
                'status' => 'aktif',
                'return_pin' => $pin,
            ]);

            $item->update(['status' => 'dipinjam']);

            return $loan;
        });
    }

    /**
     * Payload: qr, pin, returned_by, return_relation (sendiri|wakil),
     * received_by (opsional, nama petugas), return_photo_path (opsional).
     *
     * @throws ValidationException
     */
    public static function returnLoan(Loan $loan, array $payload): void
    {
        DB::transaction(function () use ($loan, $payload) {
            /** @var Loan $lockedLoan */
            $lockedLoan = Loan::lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'qr' => 'Peminjaman ini sudah ditutup sebelumnya.',
                ]);
            }

            /** @var AssetItem $item */
            $item = AssetItem::lockForUpdate()->findOrFail($lockedLoan->asset_item_id);

            if (trim($item->nomor_seri_atau_qr) !== trim((string) ($payload['qr'] ?? ''))) {
                throw ValidationException::withMessages([
                    'qr' => 'QR tidak cocok dengan alat yang dipinjam.',
                ]);
            }

            if ($lockedLoan->return_pin && trim((string) ($payload['pin'] ?? '')) !== $lockedLoan->return_pin) {
                throw ValidationException::withMessages([
                    'pin' => 'PIN pengembalian salah. Minta PIN ke peminjam atau petugas.',
                ]);
            }

            $returnedBy = trim((string) ($payload['returned_by'] ?? ''));

            if ($returnedBy === '') {
                throw ValidationException::withMessages([
                    'returned_by' => 'Nama pengembali wajib diisi.',
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
        });
    }

    private static function newPin(): string
    {
        return sprintf('%06d', random_int(0, 999999));
    }
}
