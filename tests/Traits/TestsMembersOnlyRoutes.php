<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Auth;

trait TestsMembersOnlyRoutes
{
    private function onlyForMembers(
        string $url,
        ?array $params = null,
        string $method = 'get'
    ): TestResponse {
        Auth::logout();

        $params ??= [];

        $this->$method($url, $params)
            ->assertRedirect(route('login'));

        $this->actingAs($this->getGuestUser())
            ->$method($url, $params)
            ->assertForbidden();

        return $this->actingAs($this->getMemberUser())
            ->$method($url, $params);
    }
}
