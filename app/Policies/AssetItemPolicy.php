<?php

namespace App\Policies;

use App\Models\AssetItem;
use App\Models\User;

class AssetItemPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->isSiswa();
    }

    public function create(User $user): bool
    {
        // Anak PKL boleh membantu menginput fisik barang baru yang masuk ke lab
        return $user->isSuperadmin() || $user->isToolman() || $user->isAnakPkl();
    }

    public function update(User $user, AssetItem $assetItem): bool
    {
        // Anak PKL boleh mengubah status kondisi barang (misal: update ke 'rusak')
        return $user->isSuperadmin() || $user->isToolman() || $user->isAnakPkl();
    }

    public function delete(User $user, AssetItem $assetItem): bool
    {
        // 🛑 Anak PKL TIDAK BOLEH menghapus unit fisik barang!
        return $user->isSuperadmin() || $user->isToolman();
    }
}