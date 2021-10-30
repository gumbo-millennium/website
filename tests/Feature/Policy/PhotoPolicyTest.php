<?php

declare(strict_types=1);

namespace Tests\Feature\Policy;

use App\Models\PhotoAlbum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PhotoPolicyTest extends TestCase
{
    /**
     * Tests the album policies.
     *
     * @param array<string> $state
     * @dataProvider albumProvider
     */
    public function test_album(array $state, bool $guest, bool $loggedIn, bool $member): void
    {
        $album = factory(PhotoAlbum::class)->states($state)->create();
        $user = factory(User::class)->create();

        // Guest, can only access public
        $this->assertSame($guest, Gate::allows('viewAny', PhotoAlbum::class), "Guest visibility doesn't match expectation.");
        $this->assertSame($guest, Gate::allows('view', $album), "Guest visibility doesn't match expectation.");
        $this->assertFalse(Gate::allows('update', PhotoAlbum::class), 'Failed preventing anyone to edit albums');
        $this->assertFalse(Gate::allows('create', $album), 'Failed preventing anyone to edit albums');
        $this->assertFalse(Gate::allows('delete', $album), 'Failed preventing anyone to edit albums');

        // User, can access public and user-only
        $this->actingAs($user);
        $this->assertSame($loggedIn, Gate::allows('viewAny', PhotoAlbum::class), "Logged in visibility doesn't match expectation.");
        $this->assertSame($loggedIn, Gate::allows('view', $album), "Logged in visibility doesn't match expectation.");
        $this->assertFalse(Gate::allows('create', $album), 'Failed asserting users cannot all edit albums.');
        $this->assertFalse(Gate::allows('update', PhotoAlbum::class), 'Failed asserting users cannot all edit albums.');
        $this->assertFalse(Gate::allows('delete', $album), 'Failed asserting users cannot all edit albums.');

        // Member, should be able to access all but hidden
        $user->assignRole('member');
        $this->assertSame($member, Gate::allows('viewAny', PhotoAlbum::class), "Member visibility doesn't match expectation.");
        $this->assertSame($member, Gate::allows('view', $album), "Member visibility doesn't match expectation.");
        $this->assertFalse(Gate::allows('create', $album), 'Failed asserting members cannot all edit albums.');
        $this->assertFalse(Gate::allows('update', PhotoAlbum::class), 'Failed asserting members cannot all edit albums.');
        $this->assertFalse(Gate::allows('delete', $album), 'Failed asserting members cannot all edit albums.');

        // Admin, should always be able to access
        $user->givePermissionTo('photo-album-admin');
        $this->assertTrue(Gate::allows('viewAny', PhotoAlbum::class), "Admin visibility doesn't match expectation.");
        $this->assertTrue(Gate::allows('view', $album), "Admin visibility doesn't match expectation.");
        $this->assertTrue(Gate::allows('create', $album), 'Failed asserting admins can manage albums.');
        $this->assertTrue(Gate::allows('update', PhotoAlbum::class), 'Failed asserting admins can manage albums.');
        $this->assertTrue(Gate::allows('delete', $album), 'Failed asserting admins can manage albums.');
    }

    public function albumProvider(): array
    {
        $this->ensureApplicationExists();

        return [
            'public' => [
                ['public'],
                true,
                true,
                true,
            ],
            'users' => [
                ['users'],
                false,
                true,
                true,
            ],
            'members' => [
                ['members'],
                false,
                false,
                true,
            ],
            'hidden' => [
                [],
                false,
                false,
                false,
            ],
        ];
    }
}
