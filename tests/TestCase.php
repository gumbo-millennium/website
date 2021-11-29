<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Traits\RefreshDatabase;

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
