<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Account;

use App\Models\Grant;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GrantsControllerTest extends TestCase
{
    use WithFaker;

    private string $grantOneTitle = '';

    private string $grantOneDesc = '';

    private string $grantTwoTitle = '';

    private string $grantTwoDesc = '';

    /**
     * @before
     */
    public function setGrantsBeforeTest(): void
    {
        $this->afterApplicationCreated(function () {
            $grants = Collection::make()
                ->push(
                    new Grant(
                        'test:one',
                        $this->grantOneTitle = $this->faker->sentence(),
                        $this->grantOneDesc = $this->faker->paragraph(),
                    ),
                )
                ->push(
                    new Grant(
                        'test:two',
                        $this->grantTwoTitle = $this->faker->sentence(),
                        $this->grantTwoDesc = $this->faker->paragraph(),
                    ),
                );

            Config::set('gumbo.account.grants', $grants);
        });
    }

    /**
     * Test rendering the grant display page.
     */
    public function test_grant_display(): void
    {
        $this->actingAs($this->getTemporaryUser());

        $this->get(route('account.grants'))
            ->assertOk()
            // Run as two separate items, in case we change the sorting function later on
            ->assertSeeInOrder([
                $this->grantOneTitle,
                $this->grantOneDesc,
            ])
            ->assertSeeInOrder([
                $this->grantTwoTitle,
                $this->grantTwoDesc,
            ]);
    }

    /**
     * Test setting grants.
     */
    public function test_setting_grants(): void
    {
        $this->actingAs($user = $this->getTemporaryUser());

        $this->post(route('account.grants'), [
            'test:one' => 'yes',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('account.grants'));

        $user = $user->fresh();
        $this->assertTrue($user->hasGrant('test:one'));
        $this->assertFalse($user->hasGrant('test:two'));
    }
}
