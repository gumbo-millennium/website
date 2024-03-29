<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;

trait TestsMembersOnlyRoutes
{
    private function onlyForMembers(
        string $url,
        ?array $params = null,
        string $method = 'get'
    ): TestResponse {
        Auth::logout();

        $params ??= [];

        $this->{$method}($url, $params)
            ->assertRedirect(route('login'));

        $this->actingAs($this->getGuestUser())
            ->{$method}($url, $params)
            ->assertForbidden();

        return $this->actingAs($this->getMemberUser())
            ->{$method}($url, $params);
    }
}
