<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class WsListenerTest extends TestCase
{
    public function test_ws_listener_present_for_authed_users(): void
    {
        $user = User::where("email", "admin@gmail.com")->firstOrFail();
        $res = $this->actingAs($user)->get("/admin");
        $res->assertOk();
        $res->assertSee("latekaje-lab", false);
        $res->assertSee("pusher-js", false);
        echo "ws-listener-ok".PHP_EOL;
    }
}
