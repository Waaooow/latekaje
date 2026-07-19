<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        // Siswa tidak boleh melihat katalog utama di panel admin
        return !$user->isSiswa(); 
    }

    public function create(User $user): bool
    {
        // Hanya Superadmin dan kamu (Toolman) yang boleh menambah master tipe alat
        return $user->isSuperadmin() || $user->isToolman();
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->isSuperadmin() || $user->isToolman();
    }

    public function delete(User $user, Asset $asset): bool
    {
        // Proteksi mutlak: Hanya Superadmin dan Toolman yang boleh menghapus data master
        return $user->isSuperadmin() || $user->isToolman();
    }
}