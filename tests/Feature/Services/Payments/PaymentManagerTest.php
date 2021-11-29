<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Payments;

use App\Contracts\Payments\PaymentManager;
use App\Facades\Payments;
use App\Models;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Tests\Fixtures\Services\DummyPaymentService;
use Tests\TestCase;

class PaymentManagerTest extends TestCase
{
    /**
     * @before
     */
    public function registerPaymentServiceOnAppStart(): void
    {
        $this->afterApplicationCreated(function () {
            Config::set([
                'gumbo.payments.providers' => [DummyPaymentService::class],
                'gumbo.payments.default' => DummyPaymentService::getName(),
            ]);

            App::singleton(DummyPaymentService::class);
        });
    }

    /**
     * Test normal functions work as expected.
     */
    public function test_proper_instantiation(): void
    {
        /** @var PaymentManager $manager */
        $manager = App::make(PaymentManager::class);

        $this->assertInstanceOf(PaymentManager::class, $manager);

        $this->assertSame($manager, Payments::getFacadeRoot());

        $this->assertInstanceOf(DummyPaymentService::class, $manager->default());
        $this->assertInstanceOf(DummyPaymentService::class, $manager->find(DummyPaymentService::getName()));
    }

    /**
     * Test finding an unavailable service throws an exception.
     */
    public function test_finding_missing_service(): void
    {
        $this->expectException(RuntimeException::class);
        Payments::find('invalid');
    }

    public function test_calls_are_forwarded_properly(): void
    {
        /** @var Models\User $user */
        $user = factory(Models\User::class)->create();

        /** @var Models\Activity $activity */
        $activity = factory(Models\Activity::class)->create();

        /** @var Models\Ticket $ticket */
        $ticket = $activity->tickets()->create([
            'title' => 'Steve',
            'price' => 10_00,
        ]);

        /** @var Models\Enrollment $enrollment */
        $enrollment = $user->enrollments()->create([
            'activity_id' => $activity->id,
            'ticket_id' => $ticket->id,
            'price' => $ticket->total_price,
        ]);

        $payment = Payments::create($enrollment);
        $this->assertInstanceOf(Models\Payment::class, $payment);
    }
}
