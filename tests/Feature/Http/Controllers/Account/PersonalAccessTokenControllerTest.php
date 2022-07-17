<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Account;

use Tests\TestCase;

class PersonalAccessTokenControllerTest extends TestCase
{
    /**
     * test fetching the index.
     */
    public function test_view_index(): void
    {
        $this->actingAs($user = $this->getTemporaryUser());

        $token1 = $user->createToken('Test Token 1')->accessToken;
        $token2 = $user->createToken('Test Token 2')->accessToken;

        $this->get(route('account.tokens.index'))
            ->assertStatus(200)
            ->assertSee($token1->name)
            ->assertSee($token2->name);
    }

    /**
     * Check creating the token shows the token value.
     */
    public function test_create_token(): void
    {
        $this->actingAs($user = $this->getTemporaryUser());

        $token1 = $user->createToken('Test Token 1')->accessToken;
        $this->assertSame(1, $user->tokens()->count(), 'Expected user to have one tokens');

        $this->get(route('account.tokens.index'))
            ->assertOk()
            ->assertSee($token1->name);

        $this->post(route('account.tokens.store'), [
            'name' => 'Test Token 2',
        ])
            ->assertRedirect(route('account.tokens.index'))
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(2, $user->tokens()->count(), 'Expected user to have two tokens');
        $token2 = $user->tokens()->latest()->first();

        // First time token value should be shown
        $this->get(route('account.tokens.index'))
            ->assertOk()
            ->assertSee($token1->name)
            ->assertSee($token2->name)
            ->assertSee("data-token-id=\"{$token2->id}\"", false);

        // Second time token should be invisible
        $this->get(route('account.tokens.index'))
            ->assertOk()
            ->assertSee($token1->name)
            ->assertSee($token2->name)
            ->assertDontSee("data-token-id=\"{$token2->id}\"", false);
    }
}
