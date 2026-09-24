<?php

namespace Tests\Feature;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CodeLoginTest extends TestCase
{
    public function test_code_login_dan_scope(): void
    {
        $st = Member::firstOrCreate(['code' => '9001'], ['name' => 'Tes ID', 'group' => 'X TJKT 1', 'aktif' => true]);
        User::updateOrCreate(
            ['code' => '9001', 'role' => 'users'],
            ['name' => 'Tes ID', 'email' => '9001@member.latekaje', 'password' => '9001', 'member_id' => $st->id],
        );

        $this->assertTrue(Auth::attempt(['email' => '9001', 'password' => '9001']), 'login ID gagal');
        $this->assertTrue(Auth::attempt(['email' => '9001@member.latekaje', 'password' => '9001']), 'login email gagal');
        $this->assertFalse(Auth::attempt(['email' => '9001', 'password' => 'salah']), 'password salah lolos!');

        $user = User::where('code', '9001')->firstOrFail();
        $this->actingAs($user);
        $sql = LoanResource::getEloquentQuery()->toSql();
        echo "scope: $sql\n";
        $this->assertStringContainsString('code', $sql);

        $st->delete();
        $user->delete();
    }
}
