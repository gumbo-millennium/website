<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ProvidesUsers;

    public function setUp(): void
    {
        // Forward
        parent::setUp();
    }

    /**
     * Creates an application if one isn't set
     *
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
