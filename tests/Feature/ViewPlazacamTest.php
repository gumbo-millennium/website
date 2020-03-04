<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\TempUserTrait;

/**
 * Tests viewing the plazacam
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class ViewPlazacamTest extends TestCase
{
    use TempUserTrait;

    /**
     * Test anonymous image retrieval
     * @return void
     */
    public function testReadAnonymous(): void
    {
        // Get URL
        $url = route('plazacam', [
            'image' => 'plaza'
        ], false);

        //  Retrieve plazacam
        $request = $this->get($url);

        // Expect a login redirect
        $request->assertRedirect(route('login'));
    }

    /**
     * Test anonymous image retrieval
     * @return void
     */
    public function testReadUser(): void
    {
        // Gets the user
        $user = $this->getUser(['guest']);

        // Get URL
        $url = route('plazacam', [
            'image' => 'plaza'
        ], false);

        //  Retrieve plazacam
        $request = $this->actingAs($user)->get($url);

        // Expect a forbidden response
        $request->assertForbidden();
    }

    /**
     * Test anonymous image retrieval
     * @return void
     */
    public function testReadMember(): void
    {
        // Gets the user
        $user = $this->getUser(['member']);

        // Get URL
        $url = route('plazacam', [
            'image' => 'plaza'
        ], false);

        //  Retrieve plazacam
        $request = $this->actingAs($user)->get($url);

        // Expect a login redirect
        $request->assertSuccessful();
        $request->assertHeader('Content-Type', 'image/jpeg');
    }
}
