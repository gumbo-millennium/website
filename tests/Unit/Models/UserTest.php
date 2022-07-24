<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Grant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_default_values(): void
    {
        $user = $this->getGuestUser();

        $this->assertInstanceOf(Collection::class, $user->grants);
        $this->assertTrue($user->grants->isEmpty(), 'User has no grants by default');
    }

    public function test_grant_reading(): void
    {
        $user = $this->getGuestUser();
        $user->grants = Collection::make([
            'test:one' => true,
            'test:two' => false,
        ]);

        // Test known values
        $this->assertTrue($user->hasGrant('test:one'));
        $this->assertFalse($user->hasGrant('test:two'));

        // Test unknown values
        $this->assertfalse($user->hasGrant('test:three'), 'Unknown value [test:three] should default to false');
        $this->assertTrue($user->hasGrant('test:four', true), 'Unknown value [test:four] should $default');
    }

    public function test_grant_updating(): void
    {
        $user = $this->getGuestUser();

        $user->grants = Collection::make([
            'test:one' => true,
            'test:two' => false,
            'test:three' => null,
        ]);

        $user->setGrant('test:four', true);

        $this->assertEquals([
            'test:one' => true,
            'test:two' => false,
            // test:three is null, should be removed
            'test:four' => true,
        ], $user->grants->all());

        $user->setGrant('test:one', null);

        $this->assertEquals([
            // test:one is set to null, should be removed
            'test:two' => false,
            'test:four' => true,
        ], $user->grants->all());
    }

    public function test_grant_config_resolving(): void
    {
        $config = Config::get('gumbo.account.grants');

        $this->assertIsIterable($config, 'Grants list should be iterable');
        $this->assertTrue(array_is_list($config), 'Grants list should be a list');

        $this->assertContainsOnlyInstancesOf(
            Grant::class,
            $config,
            'Grants list should contain only Grant instances',
        );
    }
}
