<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Account;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ApiTokenControllerTest extends TestCase
{
    use WithFaker;

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

    public function test_index_shows_only_own_tokens(): void
    {
        $user = $this->getTemporaryUser();
        $otherUser = $this->getTemporaryUser();

        $accessToken1 = $user->createToken($this->faker->unique()->sentence())->accessToken;
        $accessToken2 = $otherUser->createToken($this->faker->unique()->sentence())->accessToken;
        $accessToken3 = $otherUser->createToken($this->faker->unique()->sentence())->accessToken;

        $this->actingAs($user);

        $this->get(route('account.tokens.index'))
            ->assertOk()
            ->assertSee($accessToken1->name)
            ->assertDontSee($accessToken2->name)
            ->assertDontSee($accessToken3->name);
    }

    /**
     * Checks if the create token view loads properly.
     */
    public function test_create_token_view(): void
    {
        $this->get(route('account.tokens.create'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->getTemporaryUser());

        $this->get(route('account.tokens.create'))
            ->assertOk();
    }

    /**
     * Check storing a token works as expected.
     */
    public function test_store_valid_token(): void
    {
        $this->post(route('account.tokens.store'), ['name' => 'Testing 1-2-3'])
            ->assertRedirect(route('login'));

        $this->actingAs($user = $this->getTemporaryUser());

        $tokenName = $this->faker->sentence;

        // Create token
        $this->post(route('account.tokens.store'), ['name' => $tokenName])
            ->assertSessionDoesntHaveErrors()
            ->assertSessionHas('created_token')
            ->assertRedirect(route('account.tokens.index'));

        // Check if token exists
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => $tokenName,
            'tokenable_type' => $user->getMorphClass(),
            'tokenable_id' => $user->id,
        ]);

        // Check if token is shown on the index page
        $plainTextToken = Session::get('created_token')?->plainTextToken;
        $this->assertNotNull($plainTextToken, 'Failed to find plain text token in session');

        $this->get(route('account.tokens.index'))
            ->assertSee($plainTextToken);
    }

    public function test_revoking_own_tokens(): void
    {
        $user = $this->getTemporaryUser();

        $this->actingAs($user);

        $accessToken = $user->createToken('Test Token 1')->accessToken;

        $this->delete(route('account.tokens.destroy', ['token' => $accessToken->id]))
            ->assertRedirect(route('account.tokens.index'));

        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => $accessToken->name,
            'tokenable_type' => $user->getMorphClass(),
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_revoking_other_peoples_tokens(): void
    {
        $user = $this->getTemporaryUser();
        $otherUser = $this->getTemporaryUser();

        $accessToken = $otherUser->createToken('Test Token 1')->accessToken;

        $this->actingAs($user);

        $this->delete(route('account.tokens.destroy', ['token' => $accessToken->id]))
            ->assertNotFound();
    }
}
