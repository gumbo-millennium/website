<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Exports;

use Tests\TestCase;

class EnrollmentExportJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
