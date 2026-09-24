<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== 'users';
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->role !== 'users';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin'], true);
    }

    public function update(User $user, Asset $asset): bool
    {
        return in_array($user->role, ['superadmin', 'admin'], true);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return in_array($user->role, ['superadmin', 'admin'], true)
            && $asset->assetItems()->count() === 0;
    }
}
