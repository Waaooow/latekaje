<?php

namespace App\Policies;

use App\Models\AssetItem;
use App\Models\User;

class AssetItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== 'siswa';
    }

    public function view(User $user, AssetItem $assetItem): bool
    {
        return $user->role !== 'siswa';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'toolman', 'anak_pkl'], true);
    }

    public function update(User $user, AssetItem $assetItem): bool
    {
        return in_array($user->role, ['superadmin', 'toolman', 'anak_pkl'], true);
    }

    public function delete(User $user, AssetItem $assetItem): bool
    {
        return in_array($user->role, ['superadmin', 'toolman'], true)
            && $assetItem->loans()->count() === 0;
    }
}
