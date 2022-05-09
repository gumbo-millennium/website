<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Gallery;

use App\Enums\AlbumVisibility;
use App\Models\Gallery\Album;
use App\Models\User;
use Tests\TestCase;

class AlbumControllerTest extends TestCase
{
    /**
     * @before
     */
    public function alwaysActAsMember(): void
    {
        $this->afterApplicationCreated(function () {
            $user = User::factory()->withRole('member')->create();

            $this->actingAs($user);
        });
    }

    public function test_index_view(): void
    {
        $publicAlbums = Album::factory(3)
            ->withPhotos()
            ->visibility(AlbumVisibility::Public)
            ->create();

        $response = $this->get(route('gallery.index'))
            ->assertOk();

        foreach ($publicAlbums as $album) {
            $response->assertSee($album->name);
        }
    }

    public function test_album_view(): void
    {
        $album = Album::factory()
            ->visibility(AlbumVisibility::Public)
            ->create();

        $this->get(route('gallery.album', $album))
            ->assertOk()
            ->assertSee($album->name);
    }
}
