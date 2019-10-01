<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Traits\TempUserTrait;

/**
 * Tests updating the plazacam
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class UpdatePlazacamTest extends TestCase
{
    use TempUserTrait;

    /**
     * Test anonymous image retrieval
     *
     * @return void
     */
    public function testReadUser(): void
    {
        // Gets the user
        $user = $this->getUser(['guest', 'member']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'camera' => 'plaza'
        ]);

        // Get a dummy file
        $file = UploadedFile::fake()->image('capture.jpg');

        //  Retrieve plazacam
        $request = $this->actingAs($user)
            ->json('PUT', $url, [
                'file' => $file
            ]);

        // Expect a forbidden response
        $request->assertForbidden();
    }

    /**
     * Test anonymous image retrieval
     *
     * @return void
     */
    public function testReadMember(): void
    {
        // Gets the all-knowning user
        $user = $this->getUser(['guest', 'dc']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'camera' => 'plaza'
        ]);

        // Get a dummy file
        $file = UploadedFile::fake()->image('capture.jpg', 800, 600);

        //  Retrieve plazacam
        $request = $this->actingAs($user)
            ->json('PUT', $url, [
                'file' => $file
            ]);

        // Expect an OK
        $request->assertSuccessful();
    }
}
