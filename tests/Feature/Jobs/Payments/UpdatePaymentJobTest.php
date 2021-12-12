<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Payments;

use App\Helpers\Str;
use App\Jobs\Payments\UpdatePaymentJob;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Tests\Fixtures\Services\DummyPaymentService;
use Tests\TestCase;

class UpdatePaymentJobTest extends TestCase
{
    /**
     * Test payments aren't updated if they're up-to-date.
     */
    public function test_stable_job_types(): void
    {
        $user = User::factory()->create();

        $payment = Payment::make([
            'provider' => DummyPaymentService::getName(),
            'price' => 5_00,
        ]);
        $payment->payable()->associate($user);
        $payment->save();

        UpdatePaymentJob::dispatch($payment);

        $payment->transaction_id = Str::random(64);
        $payment->paid_at = Date::now();
        $payment->save();

        UpdatePaymentJob::dispatch($payment);

        $this->getDummyService()->assertWasNotSeen($payment);
    }

    public function test_paid_payment(): void
    {
        $user = User::factory()->create();

        $payment = Payment::make([
            'provider' => DummyPaymentService::getName(),
            'transaction_id' => 'Steve',
            'price' => 5_00,
        ]);
        $payment->payable()->associate($user);
        $payment->save();

        $this->getDummyService()->setProperty($payment, 'paid', true);

        UpdatePaymentJob::dispatch($payment);

        $payment->refresh();

        $this->assertNotNull($payment->paid_at);

        $this->assertNull($payment->cancelled_at);
        $this->assertNull($payment->expired_at);

        $this->getDummyService()->assertWasSeen($payment);
        $this->getDummyService()->assertWasChecked($payment, 'paid');
        $this->getDummyService()->assertWasChecked($payment, 'cancelled');
        $this->getDummyService()->assertWasChecked($payment, 'expired');
    }

    private function getDummyService(): DummyPaymentService
    {
        return App::make(DummyPaymentService::class);
    }
}
