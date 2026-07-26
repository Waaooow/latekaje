<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 🟢 REVISI: Menambahkan 'role' ke dalam daftar Fillable Attributes PHP
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * 🌟 AUTO ROLE SUPERADMIN:
     * Mencegat proses registrasi. Jika database masih kosong (0 user),
     * user pertama yang mendaftar langsung dinobatkan sebagai 'superadmin'.
     */
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (static::count() === 0) {
                $user->role = 'superadmin';
            }
        });
    }

    /**
     * 🟢 FILAMENT PANEL ACCESS:
     * Menentukan siapa saja yang punya izin untuk login ke dashboard Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Mengizinkan semua pengguna terdaftar untuk melewati gerbang login dasar.
        // Pembatasan menu spesifik akan dikendalikan lebih detail via Laravel Policy.
        return true; 
    }

    /**
     * 🟢 ROLE HELPER FUNCTIONS:
     * Fungsi instan untuk mempermudah pengecekan hak akses di seluruh sistem.
     */
    public function isSuperadmin(): bool { return $this->role === 'superadmin'; }
    public function isToolman(): bool { return $this->role === 'toolman'; }
    public function isAnakPkl(): bool { return $this->role === 'anak_pkl'; }
    public function isSiswa(): bool { return $this->role === 'siswa'; }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}