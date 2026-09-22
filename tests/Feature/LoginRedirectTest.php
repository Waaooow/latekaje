<?php

namespace Tests\Feature;

use App\Http\Responses\LoginResponse;
use App\Models\User;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    public function test_siswa_langsung_ke_peminjaman(): void
    {
        $siswa = User::firstOrCreate(
            ['email' => 'siswa-tes@latekaje.net'],
            ['name' => 'Siswa Tes', 'password' => bcrypt('x'), 'role' => 'siswa'],
        );
        $this->actingAs($siswa);

        $target = (new LoginResponse)->toResponse(request())->getTargetUrl();
        echo "siswa -> $target\n";
        $this->assertStringEndsWith('/admin/loans', $target);

        $admin = User::where('email', 'admin@latekaje.net')->first()
            ?? User::where('email', 'admin@gmail.com')->firstOrFail();
        $this->actingAs($admin);
        $target = (new LoginResponse)->toResponse(request())->getTargetUrl();
        echo "staf -> $target\n";
        $this->assertStringEndsWith('/admin', rtrim($target, '/'));
    }
}
