<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Traits\TempUserTrait;

/**
 * Tests API access to the plazacam.
 */
class ViewApiPlazacamTest extends TestCase
{
    use TempUserTrait;

    /**
     * Test anonymous image retrieval.
     */
    public function test_read_anonymous(): void
    {
        // Get URL
        $url = URL::signedRoute('api.plazacam.view', [
            'user' => 29839,
            'image' => 'plaza',
        ]);

        //  Retrieve plazacam
        $request = $this->get($url);

        // Expect a login redirect
        $request->assertNotFound();
    }

    /**
     * Test anonymous image retrieval.
     */
    public function test_read_user(): void
    {
        // Gets the user
        $user = $this->getUser(['guest']);

        // Get URL
        $url = URL::signedRoute('api.plazacam.view', [
            'user' => $user->id,
            'image' => 'plaza',
        ]);

        //  Retrieve plazacam
        $request = $this->get($url);

        // Expect a forbidden response
        $request->assertForbidden();
    }

    /**
     * Test anonymous image retrieval.
     */
    public function test_read_member(): void
    {
        // Gets the user
        $user = $this->getUser(['guest', 'member']);

        // Get URL
        $url = URL::signedRoute('api.plazacam.view', [
            'user' => $user->id,
            'image' => 'plaza',
        ]);

        //  Retrieve plazacam
        $request = $this->get($url);

        // Expect a login redirect
        $request->assertSuccessful();
        $request->assertHeader('Content-Type', 'image/jpeg');
    }
}
