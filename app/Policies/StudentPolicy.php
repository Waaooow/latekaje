<?php

namespace App\Policies;

use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isSiswa();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'toolman', 'anak_pkl'], true);
    }

    public function update(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'toolman', 'anak_pkl'], true);
    }

    public function delete(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'toolman'], true);
    }
}
