<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Command\Gumbo\User;

use App\Models\User;
use Tests\TestCase;

class SetLockedTest extends TestCase
{
    public function test_user_validation_prompt(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isLocked());

        $this->artisan('gumbo:user:lock', ['user' => $user->email])
            ->expectsConfirmation('Is this the correct user', 'no')
            ->assertExitCode(1);

        $this->assertFalse($user->fresh()->isLocked());
    }

    /**
     * Test account locking.
     */
    public function test_account_locking(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isLocked());

        $this->artisan('gumbo:user:lock', ['user' => $user->email])
            ->expectsConfirmation('Is this the correct user', 'yes')
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->isLocked());

        $this->assertNotEquals(
            $user->getRememberToken(),
            $user->fresh()->getRememberToken(),
        );
    }

    /**
     * Test account unlocking.
     */
    public function test_account_unlocking(): void
    {
        $user = User::factory()->create([
            'locked' => true,
        ]);

        $this->assertTrue($user->isLocked());

        $this->artisan('gumbo:user:lock', [
            'user' => $user->email,
            '--unlock' => true,
        ])
            ->expectsConfirmation('Is this the correct user', 'yes')
            ->assertExitCode(0);

        $this->assertFalse($user->fresh()->isLocked());
    }
}
