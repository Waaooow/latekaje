<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class StudentLoanVisibilityTest extends TestCase
{
    public function test_pinjam_hitung_aktif_dan_pin_milik_sendiri(): void
    {
        $st = Student::firstOrCreate(
            ['nis' => '9002'],
            ['nama' => 'Tes Visibilitas', 'kelas' => 'X TJKT 1', 'aktif' => true]
        );
        // Idempoten: bersihkan sisa run sebelumnya yang gagal di tengah jalan.
        Loan::where('nis', '9002')->delete();
        $user = User::updateOrCreate(
            ['nis' => '9002', 'role' => 'siswa'],
            ['name' => 'Tes Visibilitas', 'email' => '9002@siswa.latekaje', 'password' => '9002', 'student_id' => $st->id],
        );

        // Hitungan aktif: buat 1 pinjaman aktif + 1 pinjaman kembali milik student yang sama.
        $aktif = Loan::create([
            'asset_item_id' => null, 'nama_siswa' => 'Tes Visibilitas', 'kelas' => 'X TJKT 1',
            'nis' => '9002', 'student_id' => $st->id, 'status' => 'aktif',
            'tanggal_pinjam' => now(), 'return_pin' => '123456',
        ]);
        $kembali = Loan::create([
            'asset_item_id' => null, 'nama_siswa' => 'Tes Visibilitas', 'kelas' => 'X TJKT 1',
            'nis' => '9002', 'student_id' => $st->id, 'status' => 'kembali',
            'tanggal_pinjam' => now()->subDays(2), 'tanggal_kembali' => now()->subDay(),
            'return_pin' => '654321',
        ]);

        $st->refresh();
        $this->assertEquals(2, $st->loans()->count(), 'total riwayat harus 2');
        $this->assertEquals(1, $st->activeLoans()->count(), 'hitungan aktif harus 1');
        $this->assertEquals(
            1,
            Student::withCount('activeLoans')->find($st->id)->active_loans_count,
            'withCount activeLoans harus 1'
        );

        // Kepemilikan PIN.
        $this->assertTrue($user->ownsLoan($aktif), 'siswa harus memiliki pinjamannya');
        $this->assertTrue($user->ownsLoan($kembali), 'siswa harus memiliki riwayatnya');

        $lain = Loan::create([
            'asset_item_id' => null, 'nama_siswa' => 'Orang Lain', 'kelas' => 'X TJKT 2',
            'nis' => '9999', 'status' => 'aktif',
            'tanggal_pinjam' => now(), 'return_pin' => '000000',
        ]);
        $this->assertFalse($user->ownsLoan($lain), 'siswa tidak boleh memiliki pinjaman orang lain');

        // Kiosk (tanpa identitas) tidak memiliki pinjaman siapa pun.
        $kiosk = User::firstOrCreate(
            ['email' => 'kiosk@latekaje.net'],
            ['name' => 'Kiosk Lab', 'password' => 'kiosk123', 'role' => 'siswa'],
        );
        $kiosk->update(['nis' => null, 'student_id' => null]);
        $this->assertFalse($kiosk->refresh()->ownsLoan($aktif), 'kiosk tidak boleh memiliki pinjaman');

        // Default nama pengembali = nama sendiri untuk siswa beridentitas.
        $this->actingAs($user->fresh());
        $defaultName = (function () {
            $u = auth()->user();
            if ($u?->isSiswa() && ($u->nis || $u->student_id)) {
                return $u->student?->nama ?? $u->name;
            }

            return null;
        })();
        $this->assertEquals('Tes Visibilitas', $defaultName, 'default nama pengembali harus nama sendiri');

        // Bersih-bersih data tes.
        $aktif->delete();
        $kembali->delete();
        $lain->delete();
        $user->delete();
        $st->delete();
    }
}
