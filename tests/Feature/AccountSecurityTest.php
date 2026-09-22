<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    public function test_akun_nonaktif_dan_acl(): void
    {
        $tool = User::firstOrCreate(
            ['email' => 'tool-tes@latekaje.net'],
            ['name' => 'Tool Tes', 'password' => 'x', 'role' => 'toolman', 'is_active' => true],
        );
        $siswa = User::firstOrCreate(
            ['email' => 'siswa-tes@latekaje.net'],
            ['name' => 'Siswa Tes', 'password' => 'x', 'role' => 'siswa', 'is_active' => true],
        );

        // 1. Nonaktifkan -> login ditolak walau password benar
        $siswa->update(['is_active' => false]);
        $this->assertFalse(\Illuminate\Support\Facades\Auth::attempt(
            ['email' => 'siswa-tes@latekaje.net', 'password' => 'x']
        ), 'akun nonaktif lolos login!');
        $siswa->update(['is_active' => true]);
        $this->assertTrue(\Illuminate\Support\Facades\Auth::attempt(
            ['email' => 'siswa-tes@latekaje.net', 'password' => 'x']
        ), 'akun aktif ditolak!');

        // 2. Token sanctum dibuat & bisa dicabut
        $token = $tool->createToken('tes', ['*']);
        $this->assertNotEmpty($token->plainTextToken);
        $this->assertEquals(1, $tool->tokens()->count());
        $tool->tokens()->delete();
        $this->assertEquals(0, $tool->tokens()->count());

        // 3. ACL deny menimpa policy
        $this->actingAs($tool);
        $item = \App\Models\AssetItem::doesntHave('loans')->firstOrFail();
        $this->assertTrue(Gate::allows('delete', $item), 'toolman seharusnya boleh hapus');
        $tool->update(['permissions' => ['deny' => ['delete:AssetItem']]]);
        $this->assertFalse(Gate::allows('delete', $item), 'deny ACL tidak berlaku!');
        $tool->update(['permissions' => null]);
        $this->assertTrue(Gate::allows('delete', $item), 'toolman seharusnya boleh hapus');
        $tool->update(['permissions' => ['deny' => ['delete:AssetItem']]]);
        $this->assertFalse(Gate::allows('delete', $item), 'deny ACL tidak berlaku!');
        $tool->update(['permissions' => null]);

        // 4. Halaman user + setting render
        $admin = User::where('email', 'admin@latekaje.net')->firstOrFail();
        $this->actingAs($admin);
        $this->get('/admin/user-resource/users')->assertOk();
        $this->get('/admin/setting')->assertOk();
        echo "account-security-ok\n";
    }
}
