<?php

namespace Tests\Feature;

use App\Http\Responses\LoginResponse;
use App\Models\User;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    public function test_anggota_langsung_ke_peminjaman(): void
    {
        $anggota = User::firstOrCreate(
            ['email' => 'anggota-tes@latekaje.net'],
            ['name' => 'Anggota Tes', 'password' => bcrypt('x'), 'role' => 'users'],
        );
        $this->actingAs($anggota);

        $target = (new LoginResponse)->toResponse(request())->getTargetUrl();
        echo "anggota -> $target\n";
        $this->assertStringEndsWith('/admin/loans', $target);

        $admin = User::where('email', 'admin@latekaje.net')->first()
            ?? User::where('email', 'admin@gmail.com')->firstOrFail();
        $this->actingAs($admin);
        $target = (new LoginResponse)->toResponse(request())->getTargetUrl();
        echo "staf -> $target\n";
        $this->assertStringEndsWith('/admin', rtrim($target, '/'));
    }
}
