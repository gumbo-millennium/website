<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileDisplayTest extends TestCase
{
    public function testViewListAsAnonymous()
    {
        // Request the index
        $response = $this->get(route('files.index'));

        // Expect a response redirecting to login
        $response->assertRedirect(route('login'));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testViewListAsGuest()
    {
        // Get a guest user
        $user = $this->getGuestUser();

        // Request the index
        $response = $this->actingAs($user)
                    ->get(route('files.index'));

        // Expect a response redirecting to login
        $response->assertForbidden();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testViewListAsMember()
    {
        // Get a guest user
        $user = $this->getMemberUser();

        // Request the index
        $response = $this->actingAs($user)
                    ->get(route('files.index'));

        // Expect an OK response
        $response->assertOk();
    }
}
