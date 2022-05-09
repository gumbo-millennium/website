<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Gallery;

use App\Enums\AlbumVisibility;
use App\Models\Gallery\Album;
use App\Models\User;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_scoping()
    {
        [$user1, $user2] = User::factory()->withRole('member')->times(2)->create();

        $publicAlbum = Album::factory()->visibility(AlbumVisibility::Public)->create();
        $privateAlbum = Album::factory()->privateFor($user2)->create();

        $this->assertEquals(
            [$publicAlbum->id],
            Album::forUser($user1)->pluck('id')->toArray(),
            'User 1 should only see public albums',
        );

        $this->assertEquals(
            [$publicAlbum->id, $privateAlbum->id],
            Album::forUser($user2)->pluck('id')->toArray(),
            'User 2 should see public and their own albums',
        );
    }
}
