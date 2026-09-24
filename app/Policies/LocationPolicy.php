<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== 'users';
    }

    public function view(User $user, Location $location): bool
    {
        return $user->role !== 'users';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin'], true);
    }

    public function update(User $user, Location $location): bool
    {
        return in_array($user->role, ['superadmin', 'admin'], true);
    }

    public function delete(User $user, Location $location): bool
    {
        return in_array($user->role, ['superadmin', 'admin'], true);
    }
}
