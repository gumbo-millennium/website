<?php

declare(strict_types=1);

namespace Tests\Unit\Account;

use App\Http\Controllers\Account\GrantsController;
use App\Models\Grant;
use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class GrantsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $user = $this->getGuestUser();

        $this->assertIsArray($user->grants);
        $this->assertEmpty($user->grants);
    }

    public function testGrantsFileIsValid(): void
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

    public function testGrantGeneratorReturnsProperValues(): void
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

    public function testGrantsPageRequiresLogin(): void
    {
        $grantsRequest = $this->get(route('account.grants'));

        $grantsRequest->assertRedirect();
    }

    public function testGrantsAreRenderedInTheForm(): void
    {
        $user = $this->getGuestUser();

        $this->actingAs($user);

        $grantsRequest = $this->get(route('account.grants'));

        $grantsRequest->assertOk();

        foreach (GrantsController::getGrants() as $grant) {
            $grantsRequest->assertSee($grant->key);
            $grantsRequest->assertSeeText($grant->name);
        }
    }

    /**
     * @depends testDefaultValues
     */
    public function testSavingGrantsWorksProperly(): void
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
