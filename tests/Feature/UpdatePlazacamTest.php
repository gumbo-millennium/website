<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Traits\TempUserTrait;

/**
 * Tests updating the plazacam
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class UpdatePlazacamTest extends TestCase
{
    use TempUserTrait;
    use RefreshDatabase;

    /**
     * Test assignment by guest
     * @return void
     */
    public function testWriteUser(): void
    {
        // Gets the user
        $user = $this->getUser(['guest']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza'
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
     * Test assignment by member
     * @return void
     */
    public function testWriteMember(): void
    {
        // Gets the all-knowning user
        $user = $this->getUser(['member']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza'
        ]);

        // Get a dummy file
        $file = UploadedFile::fake()->image('capture.jpg', 800, 600);

        //  Retrieve plazacam
        $request = $this->actingAs($user)
            ->json('PUT', $url, [
                'file' => $file
            ]);

        // Expect an OK
        $request->assertForbidden();
    }

    /**
     * Test assignment by PC
     * @return void
     */
    public function testWritePlazaCommittee(): void
    {
        // Gets the all-knowning user
        $user = $this->getUser(['member', 'pc']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza'
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

    /**
     * Test assignment by board
     * @return void
     */
    public function testWriteBoard(): void
    {
        // Gets the all-knowning user
        $user = $this->getUser(['member', 'board']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza'
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
