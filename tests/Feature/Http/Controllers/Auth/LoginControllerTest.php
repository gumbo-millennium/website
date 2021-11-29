<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    public function test_logout_redirect(): void
    {
        Date::setTestNow('2021-11-25T23:30:00');

        $user = factory(User::class)->create();

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
