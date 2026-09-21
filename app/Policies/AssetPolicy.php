<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== 'siswa';
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->role !== 'siswa';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'toolman'], true);
    }

    public function update(User $user, Asset $asset): bool
    {
        return in_array($user->role, ['superadmin', 'toolman'], true);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return in_array($user->role, ['superadmin', 'toolman'], true)
            && $asset->assetItems()->count() === 0;
    }
}
