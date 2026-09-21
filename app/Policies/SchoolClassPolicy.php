<?php

namespace App\Policies;

use App\Models\User;

class SchoolClassPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isSiswa();
    }

    public function create(User $user): bool
    {
        return $user->isSuperadmin() || $user->isToolman();
    }

    public function update(User $user): bool
    {
        return $user->isSuperadmin() || $user->isToolman();
    }

    public function delete(User $user): bool
    {
        return $user->isSuperadmin() || $user->isToolman();
    }
}
