<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperadmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperadmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isSuperadmin();
    }

    public function delete(User $user, User $model): bool
    {
        // Superadmin bisa hapus user lain, TAPI tidak bisa hapus dirinya sendiri
        return $user->role === 'superadmin' && $user->id !== $model->id;
    }
}
