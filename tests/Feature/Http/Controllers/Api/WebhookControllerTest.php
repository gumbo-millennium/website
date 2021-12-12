<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Helpers\Str;
use App\Jobs\Payments\UpdatePaymentJob;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\MolliePaymentService;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    public function test_get_without_id(): void
    {
        Bus::fake();

        $this->post(route('api.webhooks.mollie'))
            ->assertOk();

        Bus::assertNotDispatched(UpdatePaymentJob::class);
    }

    public function test_get_invalid_id(): void
    {
        Bus::fake();

        $this->post(route('api.webhooks.mollie'), [
            'id' => Str::random(64),
        ])->assertOk();

        Bus::assertNotDispatched(UpdatePaymentJob::class);
    }

    public function test_get_valid_id(): void
    {
        $user = User::factory()->create();

        $payment = Payment::make([
            'provider' => MolliePaymentService::getName(),
            'transaction_id' => Str::random(64),
            'price' => 4_00,
        ]);
        $payment->payable()->associate($user);
        $payment->save();

        Bus::fake();

        $this->post(route('api.webhooks.mollie'), [
            'id' => $payment->transaction_id,
        ])->assertOk();

        Bus::assertDispatched(UpdatePaymentJob::class, fn (UpdatePaymentJob $job) => $payment->is($job->getPayment()));
    }
}
