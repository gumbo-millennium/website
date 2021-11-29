<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EnrollNew;

use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
