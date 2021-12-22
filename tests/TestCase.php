<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use App\Services\Payments\MolliePaymentService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Fixtures\Services\DummyPaymentService;
use Tests\Traits\RefreshDatabase;

/**
 * @method void actingAs(User $user)
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ProvidesUsers;
    use RefreshDatabase;

    public function setUp(): void
    {
        // Forward
        parent::setUp();

        // Disable Mix
        $this->withoutMix();

        // Register singletons
        $this->app->singleton(DummyPaymentService::class);

        // Update payment provider
        Config::set([
            'gumbo.payments.default' => DummyPaymentService::class,
            'gumbo.payments.providers' => [
                MolliePaymentService::class,
                DummyPaymentService::class,
            ],
        ]);

        // Bind response helper
        TestResponse::macro('dumpOnError', function () {
            /** @var TestResponse $this */
            if ($this->getStatusCode() >= 500) {
                $this->dump();
            }

            return $this;
        });
    }

    /**
     * Creates an application if one isn't set.
     */
    public function ensureApplicationExists(): void
    {
        // Create app if one hasn't been created yet
        if ($this->app !== null) {
            return;
        }

        $this->refreshApplication();
    }
}
