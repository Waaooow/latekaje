<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DumpReviewTest extends TestCase
{
    public function test_dump(): void
    {
        $email = env('REVIEW_USER_EMAIL', 'admin@gmail.com');
        $u = User::where('email', $email)->first();

        if (! $u) {
            $this->markTestSkipped("review user $email tidak ada (test review dev).");
        }
        $this->actingAs($u);
        $pages = [
            '/admin' => 'dashboard',
            '/admin/loans' => 'loans',
            '/admin/loans/create' => 'loan-create',
            '/admin/asset-items' => 'items',
            '/admin/assets' => 'assets',
            '/admin/locations' => 'locations',
            '/admin/members' => 'students',
            '/admin/groups' => 'classes',
            '/admin/data-per-lokasi' => 'lokasi',
            '/admin/setting' => 'notif',
        ];
        @mkdir('/tmp/review', 0777, true);
        foreach ($pages as $url => $name) {
            $res = $this->get($url);
            file_put_contents("/tmp/review/$name.html", $res->getContent());
            echo "$name: ".$res->getStatusCode().' '.strlen($res->getContent())."\n";
        }
        $this->assertTrue(true);
    }
}
