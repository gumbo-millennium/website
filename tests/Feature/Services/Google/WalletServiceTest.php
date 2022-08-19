<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Google;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\Google\WalletObjects;
use App\Services\Google\WalletService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    /**
     * @before
     */
    public function setupIssuerIdAndTraitBeforeTest(): void
    {
        $this->afterApplicationCreated(fn () => Config::set('services.google.wallet.issuer_id', '1001337'));
    }

    /**
     * Test core functionality of the WalletService.
     */
    public function test_initialisation(): void
    {
        /** @var WalletService $service */
        $service = App::make(WalletService::class);

        $this->assertInstanceOf(WalletService::class, $service);

        $user = User::factory()->create();
        $activity = Activity::factory()->withTickets()->create();
        $enrollment = $activity->enrollments()->save(
            Enrollment::factory()
                ->for($user)
                ->for($activity->tickets->first())
                ->create(),
        );

        $this->assertInstanceOf(WalletObjects\EventTicketClass::class, $service->makeActivityTicketClass($activity));
        $this->assertInstanceOf(WalletObjects\EventTicketObject::class, $service->makeEnrollmentTicketObject($enrollment));
    }
}
