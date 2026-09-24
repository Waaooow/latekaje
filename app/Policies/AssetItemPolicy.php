<?php

namespace App\Policies;

use App\Models\AssetItem;
use App\Models\User;

class AssetItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== 'users';
    }

    public function view(User $user, AssetItem $assetItem): bool
    {
        return $user->role !== 'users';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin', 'staff', 'assistant'], true);
    }

    public function update(User $user, AssetItem $assetItem): bool
    {
        return in_array($user->role, ['superadmin', 'admin', 'staff', 'assistant'], true);
    }

    public function delete(User $user, AssetItem $assetItem): bool
    {
        // Boleh hapus bila tidak ada pinjaman AKTIF. Riwayat yang sudah
        // kembali tetap tersimpan (asset_item_id di-set NULL).
        return in_array($user->role, ['superadmin', 'admin'], true)
            && ! $assetItem->loans()->where('status', 'aktif')->exists();
    }
}
