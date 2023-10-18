<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * Test the /me call.
     */
    public function test_whoami()
    {
        $this->get('/api/me')
            ->assertRedirect(route('login'));

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->get('/api/me')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                ],
            ]);
    }
}
