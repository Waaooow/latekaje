<?php

namespace App\Policies;

use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isBorrower();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin', 'staff', 'assistant'], true);
    }

    public function update(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin', 'staff', 'assistant'], true);
    }

    public function delete(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin'], true);
    }
}
