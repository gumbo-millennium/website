<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use ProvidesUsers;

    public function setUp(): void
    {
        // Permissions are super required, so always seed them when using a DB
        $this->afterApplicationCreated(function () {
            $this->artisan('db:seed --class=PermissionSeeder');
        });

        // Forward
        parent::setUp();
    }

    /**
     * Creates an application if one isn't set
     * @return void
     */
    public function ensureApplicationExists(): void
    {
        // Create app if one hasn't been created yet
        if ($this->app === null) {
            $this->refreshApplication();
        }
    }
}
