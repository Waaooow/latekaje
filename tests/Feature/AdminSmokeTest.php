<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    public function test_admin_pages_render(): void
    {
        $user = User::where("email", "admin@latekaje.net")->first() ?? User::where("email", "admin@gmail.com")->firstOrFail();
        $this->actingAs($user);

        foreach (["/admin", "/admin/assets", "/admin/asset-items", "/admin/loans", "/admin/loans/create", "/admin/locations", "/admin/data-per-lokasi", "/admin/about-app", "/admin/user-resource/users"] as $url) {
            $res = $this->get($url);
            $this->assertNotEquals(500, $res->getStatusCode(), "FAILED: $url => ".$res->getStatusCode());
            echo "$url => ".$res->getStatusCode().PHP_EOL;
        }

        $res = $this->get("/print-qr?ids=all");
        $this->assertEquals(200, $res->getStatusCode(), "print-qr failed");
        echo "/print-qr?ids=all => 200".PHP_EOL;
    }
}
