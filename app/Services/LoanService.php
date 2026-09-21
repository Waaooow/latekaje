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
    public static function borrow(int $itemId, string $nama, string $kelas): Loan
    {
        return DB::transaction(function () use ($itemId, $nama, $kelas) {
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
            ]);

            $item->update(['status' => 'dipinjam']);

            return $loan;
        });
    }

    /**
     * @throws ValidationException
     */
    public static function returnLoan(Loan $loan, string $qr, string $nama, string $kelas): void
    {
        DB::transaction(function () use ($loan, $qr, $nama, $kelas) {
            /** @var Loan $lockedLoan */
            $lockedLoan = Loan::lockForUpdate()->findOrFail($loan->id);

            /** @var AssetItem $item */
            $item = AssetItem::lockForUpdate()->findOrFail($lockedLoan->asset_item_id);

            if (trim($item->nomor_seri_atau_qr) !== trim($qr)) {
                throw ValidationException::withMessages([
                    'qr' => 'QR tidak cocok dengan alat yang dipinjam.',
                ]);
            }

            $expectedNama = mb_strtolower(trim($lockedLoan->nama_siswa));
            $givenNama = mb_strtolower(trim($nama));
            $expectedKelas = mb_strtolower(trim($lockedLoan->kelas));
            $givenKelas = mb_strtolower(trim($kelas));

            if ($expectedNama !== $givenNama || $expectedKelas !== $givenKelas) {
                throw ValidationException::withMessages([
                    'nama_siswa' => 'Nama atau kelas tidak cocok dengan data peminjaman.',
                ]);
            }

            $lockedLoan->update([
                'status' => 'kembali',
                'tanggal_kembali' => now(),
            ]);

            $item->update(['status' => 'tersedia']);
        });
    }
}
