<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Traits\TempUserTrait;

/**
 * Tests updating the plazacam.
 */
class UpdatePlazacamTest extends TestCase
{
    use TempUserTrait;

    /**
     * Test assignment by guest.
     */
    public function test_write_user(): void
    {
        // Gets the user
        $user = $this->getUser(['guest']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza',
        ]);

        // Get a dummy file
        $file = UploadedFile::fake()->image('capture.jpg');

        //  Retrieve plazacam
        $request = $this->actingAs($user)
            ->json('PUT', $url, [
                'file' => $file,
            ]);

        // Expect a forbidden response
        $request->assertForbidden();
    }

    /**
     * Test assignment by member.
     */
    public function test_write_member(): void
    {
        // Gets the all-knowning user
        $user = $this->getUser(['member']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza',
        ]);

        // Get a dummy file
        $file = UploadedFile::fake()->image('capture.jpg', 800, 600);

        //  Retrieve plazacam
        $request = $this->actingAs($user)
            ->json('PUT', $url, [
                'file' => $file,
            ]);

        // Expect an OK
        $request->assertForbidden();
    }

    /**
     * Test assignment by PC.
     */
    public function test_write_plaza_committee(): void
    {
        // Gets the all-knowning user
        $user = $this->getUser(['member', 'pc']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza',
        ]);

        // Get a dummy file
        $file = UploadedFile::fake()->image('capture.jpg', 800, 600);

        //  Retrieve plazacam
        $request = $this->actingAs($user)
            ->json('PUT', $url, [
                'file' => $file,
            ]);

        // Expect an OK
        $request->assertSuccessful();
    }

    /**
     * Test assignment by board.
     */
    public function test_write_board(): void
    {
        // Gets the all-knowning user
        $user = $this->getUser(['member', 'board']);

        // Get the url
        $url = URL::signedRoute('api.plazacam.store', [
            'user' => $user->id,
            'image' => 'plaza',
        ]);

        // Get a dummy file
        $file = UploadedFile::fake()->image('capture.jpg', 800, 600);

        //  Retrieve plazacam
        $request = $this->actingAs($user)
            ->json('PUT', $url, [
                'file' => $file,
            ]);

        // Expect an OK
        $request->assertSuccessful();
    }
}
