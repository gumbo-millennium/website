<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Activity;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    /**
     * @before
     */
    public function disableRefreshesBeforeTest(): void
    {
        $this->afterApplicationCreated(fn () => Config::set([
            'gumbo.payments.verify.refresh_rate' => 10, // Set refresh to 10ms
            'gumbo.payments.verify.timeout' => 10, // Wait a maximum of 10ms
        ]));
    }

    /**
     * Test data confidentiality.
     */
    public function test_show_authorisation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $activity = Activity::factory()->create();

        $payment = Payment::factory()->subject($activity)->for($user1)->create();
        $paymentShowRoute = route('payment.show', $payment);

        $this->get($paymentShowRoute)->assertRedirect(route('login'));

        $this->actingAs($user1);
        $this->get($paymentShowRoute)->assertOk();

        $this->actingAs($user2);
        $this->get($paymentShowRoute)->assertNotFound();
    }

    /**
     * Check if the parameters in the URL and in the payment state
     * are properly set.
     */
    public function test_show_states(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create();
        $payment = Payment::factory()->subject($activity)->for($user)->create();

        $this->actingAs($user);

        $showRoute = route('payment.show', $payment);
        $verifyRoute = route('payment.verify', $payment);
        $redirectRoute = route('payment.redirect', $payment);

        $this->get($showRoute)
            ->assertOk()
            ->assertHeader('Refresh', "0; url={$redirectRoute}")
            ->assertViewIs('payment.wait-redirect');

        $this->get("{$showRoute}?verify=1")
            ->assertOk()
            ->assertHeader('Refresh', "0; url={$verifyRoute}")
            ->assertViewIs('payment.wait-verify');

        $payment->paid_at = now();
        $payment->save();

        $this->get($showRoute)
            ->assertRedirect($verifyRoute);
    }
}
