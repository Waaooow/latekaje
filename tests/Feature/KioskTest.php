<?php

namespace Tests\Feature;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class KioskTest extends TestCase
{
    public function test_kiosk_generik_normal(): void
    {
        $kiosk = User::firstOrCreate(
            ['email' => 'kiosk@latekaje.net'],
            ['name' => 'Kiosk Lab', 'password' => 'kiosk123', 'role' => 'users'],
        );
        // Self-heal stale rows from earlier runs (firstOrCreate never overwrites).
        $kiosk->forceFill(['password' => 'kiosk123', 'role' => 'users'])->save();
        $kiosk->update(['code' => null, 'member_id' => null]);

        $this->assertTrue(Auth::attempt(['email' => 'kiosk@latekaje.net', 'password' => 'kiosk123']), 'login kiosk gagal');
        $this->actingAs($kiosk);

        $sql = LoanResource::getEloquentQuery()->toSql();
        echo "kiosk scope: $sql\n";
        $this->assertStringNotContainsString('code', $sql, 'kiosk harus lihat semua');

        $target = (new \App\Http\Responses\LoginResponse)->toResponse(request())->getTargetUrl();
        $this->assertStringEndsWith('/admin/loans', $target);

        $res = $this->get('/admin/loans/create');
        $res->assertOk();
        echo "kiosk form: ".$res->getStatusCode()."\n";
    }
}
