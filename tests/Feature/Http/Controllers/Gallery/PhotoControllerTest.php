<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Gallery;

use Tests\TestCase;

class PhotoControllerTest extends TestCase
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
