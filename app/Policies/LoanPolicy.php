<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        // Semua user termasuk siswa bisa akses menu ini (Siswa untuk lihat pinjaman mereka)
        return true;
    }

    public function view(User $user, Loan $loan): bool
    {
        // Izinkan semua logged-in user melihat detail data (karena akun siswa dipakai bersama)
        return true;
    }

    public function create(User $user): bool
    {
        // Petugas (Kasir) & Siswa (Mandiri request) boleh membuat transaksi peminjaman
        return true;
    }

    public function update(User $user, Loan $loan): bool
    {
        // Siswa dilarang mengubah/mengedit data peminjaman yang sudah dibuat.
        // Hanya kamu (Toolman), Anak PKL, dan Superadmin yang bisa edit statusnya jadi 'kembali'.
        return !$user->isSiswa();
    }

    public function delete(User $user, Loan $loan): bool
    {
        // Hanya tingkatan tertinggi yang boleh menghapus riwayat log transaksi
        return $user->isSuperadmin() || $user->isToolman();
    }
}
