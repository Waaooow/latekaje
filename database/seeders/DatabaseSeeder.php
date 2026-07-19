<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 👑 OTOMATISASI SUPERADMIN SAAT DEPLOY
        User::firstOrCreate(
            // Kunci unik untuk pengecekan data (Email)
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@gmail.com')],
            // Data yang akan diinput jika email di atas belum terdaftar
            [
                'name' => env('SUPERADMIN_NAME', 'Super Admin LATEKAJE'),
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password123')), // Ubah sesuai keinginan
                'role' => 'superadmin',
            ]
        );
    }
}