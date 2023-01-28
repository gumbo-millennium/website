<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    /**
     * Check if login works as expected and sets the "gumbo_logged_in" cookie to signal
     * the next time the user has logged in before.
     */
    public function test_login_return_cookie_assignment(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('home'))
            ->assertCookie('gumbo_logged_in');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Check if the locked-flag is respected.
     */
    public function test_account_locking(): void
    {
        [$lockedUser, $unlockedUser] = User::factory()->createMany([
            ['locked' => true],
            ['locked' => false],
        ]);

        $this->post(route('login'), [
            'email' => $lockedUser->email,
            'password' => 'password',
        ])->assertRedirect(route('login'));

        $this->assertGuest();

        $flashMessage = flash()->getMessage();
        $this->assertNotNull($flashMessage);
        $this->assertSame(
            __('Your account has been locked. Please contact the board to unlock your account.'),
            $flashMessage->message,
        );

        $this->post(route('login'), [
            'email' => $unlockedUser->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($unlockedUser);
    }

    public function test_logout_redirect(): void
    {
        Date::setTestNow('2021-11-25T23:30:00');

        $user = User::factory()->create();

        // No next
        $this->actingAs($user);
        $this->post(route('logout'))
            ->assertRedirect(URL::temporarySignedRoute('logout.done', Date::now()->addMinute()));

        // Local next
        $this->actingAs($user);
        $this->post(route('logout'), [
            'next' => url('/test-url'),
        ])->assertRedirect(URL::temporarySignedRoute('logout.done', Date::now()->addMinute(), [
            'next' => url('/test-url'),
        ]));

        // External (possibly CSRF-attacked) next
        $this->actingAs($user);
        $this->post(route('logout'), [
            'next' => 'http://example.com/test-example',
        ])->assertRedirect(URL::temporarySignedRoute('logout.done', Date::now()->addMinute()));
    }

    public function test_logout_view(): void
    {
        $this->get(URL::signedRoute('logout.done'))
            ->assertOk()
            ->assertViewIs('auth.logout')
            ->assertSee(__('Homepage'))
            ->assertDontSee(__('Continue'));

        $this->get(URL::signedRoute('logout.done', [
            'next' => url('/steve'),
        ]))
            ->assertOk()
            ->assertViewIs('auth.logout')
            ->assertSee(__('Homepage'))
            ->assertSee(__('Continue'));
    }
}
