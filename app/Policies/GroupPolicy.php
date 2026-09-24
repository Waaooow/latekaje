<?php

namespace App\Policies;

use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isBorrower();
    }

    public function create(User $user): bool
    {
        return $user->isSuperadmin() || $user->isAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isSuperadmin() || $user->isAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isSuperadmin() || $user->isAdmin();
    }
}
