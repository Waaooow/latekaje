<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class NisUserProvider extends EloquentUserProvider
{
    /**
     * Login menerima NIS atau email di kolom yang sama.
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return null;
        }

        $login = $credentials['email'] ?? null;

        if (blank($login)) {
            return null;
        }

        $query = $this->newModelQuery()
            ->where('email', $login)
            ->orWhere('nis', $login);

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            if (in_array($key, ['email', 'nis'], true)) {
                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }

    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool
    {
        // Akun dinonaktifkan tidak bisa login walau password benar.
        if ($user instanceof \App\Models\User && ! $user->is_active) {
            return false;
        }

        return parent::validateCredentials($user, $credentials);
    }
}
