<?php

declare(strict_types=1);

namespace Tests\Unit\Account;

use App\Http\Controllers\Account\GrantsController;
use App\Models\Grant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class GrantsTest extends TestCase
{
    public function test_default_values(): void
    {
        $user = $this->getGuestUser();

        $this->assertIsArray($user->grants);
        $this->assertEmpty($user->grants);
    }

    public function test_grants_file_is_valid(): void
    {
        $grantFile = resource_path(GrantsController::GRANTS_FILE);
        $fileGrants = Yaml::parseFile($grantFile);

        foreach ($fileGrants as $grant => $values) {
            $this->assertIsArray($values);
            $this->assertIsString($grant);
            $this->assertArrayHasKey('name', $values);
            $this->assertArrayHasKey('description', $values);
        }
    }

    public function test_grant_generator_returns_proper_values(): void
    {
        $grantFile = resource_path(GrantsController::GRANTS_FILE);
        $fileGrants = Yaml::parseFile($grantFile);

        $grantModels = [...GrantsController::getGrants()];

        $this->assertCount(count($fileGrants), $grantModels);
        $this->assertContainsOnlyInstancesOf(Grant::class, $grantModels);

        $keyedModels = array_combine(Arr::pluck($grantModels, 'key'), $grantModels);

        foreach ($fileGrants as $key => $value) {
            $this->assertArrayHasKey($key, $keyedModels);
            $this->assertEquals($value['name'], $keyedModels[$key]->name);
        }
    }

    public function test_grants_page_requires_login(): void
    {
        $grantsRequest = $this->get(route('account.grants'));

        $grantsRequest->assertRedirect();
    }

    public function test_grants_are_rendered_in_the_form(): void
    {
        $user = $this->getGuestUser();

        $this->actingAs($user);

        $this->assertTrue($user->is(Auth::user()), 'User is not the same as Auth::user()');

        $grantsRequest = $this->get(route('account.grants'))
            ->assertOk();

        foreach (GrantsController::getGrants() as $grant) {
            $grantsRequest->assertSee($grant->key);
            $grantsRequest->assertSee($grant->name);
        }
    }

    /**
     * @depends test_default_values
     */
    public function test_saving_grants_works_properly(): void
    {
        $user = $this->getGuestUser();

        $this->assertEmpty($user->grants);

        $this->actingAs($user);

        $grantModels = [...GrantsController::getGrants()];
        $granted = array_pop($grantModels);

        $grantsRequest = $this->post(route('account.grants'), [
            $granted->key => '1',
        ]);

        $grantsRequest->assertRedirect(route('account.index'));

        $user->refresh();

        foreach ($grantModels as $grant) {
            $this->assertArrayHasKey($grant->key, $user->grants);
            $this->assertFalse($user->grants[$grant->key]);

            // Check that the grant is explicitly false, and not unset
            $this->assertFalse($user->hasGrant($grant->key, true));
        }

        $this->assertArrayHasKey($granted->key, $user->grants);
        $this->assertTrue($user->grants[$granted->key]);

        // Check that the grant check is true
        $this->assertTrue($user->hasGrant($granted->key, false));
    }
}
