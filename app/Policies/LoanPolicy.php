<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Loan $loan): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->role !== 'siswa';
    }

    public function delete(User $user, Loan $loan): bool
    {
        return in_array($user->role, ['superadmin', 'toolman'], true);
    }
}
