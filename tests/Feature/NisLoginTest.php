<?php

namespace Tests\Feature;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Loan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NisLoginTest extends TestCase
{
    public function test_nis_login_dan_scope(): void
    {
        $st = Student::firstOrCreate(['nis' => '9001'], ['nama' => 'Tes Nis', 'kelas' => 'X TJKT 1', 'aktif' => true]);
        User::updateOrCreate(
            ['nis' => '9001', 'role' => 'siswa'],
            ['name' => 'Tes Nis', 'email' => '9001@siswa.latekaje', 'password' => '9001', 'student_id' => $st->id],
        );

        $this->assertTrue(Auth::attempt(['email' => '9001', 'password' => '9001']), 'login NIS gagal');
        $this->assertTrue(Auth::attempt(['email' => '9001@siswa.latekaje', 'password' => '9001']), 'login email gagal');
        $this->assertFalse(Auth::attempt(['email' => '9001', 'password' => 'salah']), 'password salah lolos!');

        $user = User::where('nis', '9001')->firstOrFail();
        $this->actingAs($user);
        $sql = LoanResource::getEloquentQuery()->toSql();
        echo "scope: $sql\n";
        $this->assertStringContainsString('nis', $sql);

        $st->delete();
        $user->delete();
    }
}
