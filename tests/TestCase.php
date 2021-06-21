<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
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
    }

    /**
     * Creates an application if one isn't set
     *
     * @return void
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
