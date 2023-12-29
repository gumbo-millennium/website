<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Conscribo;

use App\Services\Conscribo\Data\EntityTypeCollection;
use App\Services\Conscribo\Enums\ConscriboErrorCodes;
use App\Services\Conscribo\Exceptions\ConscriboException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use LogicException;

class ClientTest extends ConscriboTestCase
{
    use WithFaker;

    /**
     * Test authentication happy flow.
     */
    public function test_auth(): void
    {
        $this->assertNull($this->cache->get('conscribo.session_id'));

        Http::fakeSequence('https://secure.example.com/testing/request.json')
            ->pushFile(test_resource('http/conscribo/auth/auth_0.json'));

        $response = $this->getClient()->request('request', []);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('requestSequence', $response);
        $this->assertEquals('user', $response['requestSequence']);

        $this->assertEquals('test-auth', $this->cache->get('conscribo.session_id'));
    }

    /**
     * Test re-authentication happy-flow.
     */
    public function test_reauth(): void
    {
        $this->cache->put('conscribo.session_id', 'testing');

        Http::fakeSequence('https://secure.example.com/testing/request.json')
            ->pushFile(test_resource('http/conscribo/auth/reauth_0.json'))
            ->pushFile(test_resource('http/conscribo/auth/reauth_1.json'));

        $response = $this->getClient()->request('request', []);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('requestSequence', $response);
        $this->assertEquals('user', $response['requestSequence']);

        $this->assertEquals('test-reauth', $this->cache->get('conscribo.session_id'));
    }

    /**
     * Test authentication unhappy flow.
     */
    public function test_auth_fail(): void
    {
        $this->assertNull($this->cache->get('conscribo.session_id'));

        Http::fakeSequence('https://secure.example.com/testing/request.json')
            ->pushFile(test_resource('http/conscribo/auth/auth_fail_0.json'));

        $this->expectException(ConscriboException::class);
        $this->expectExceptionCode(ConscriboErrorCodes::AuthInvalid->value);
        $this->getClient()->request('request', []);
    }

    /**
     * Test re-authentication happy-flow.
     */
    public function test_reauth_fail(): void
    {
        $this->cache->put('conscribo.session_id', 'testing');

        Http::fakeSequence('https://secure.example.com/testing/request.json')
            ->pushFile(test_resource('http/conscribo/auth/reauth_0.json'))
            ->pushFile(test_resource('http/conscribo/auth/auth_fail_0.json'));

        $this->expectException(ConscriboException::class);
        $this->expectExceptionCode(ConscriboErrorCodes::AuthInvalid->value);
        $this->getClient()->request('request', []);
    }

    public function test_resource_mapping(): void
    {
        $this->config->set('conscribo.user_resource', $resourceName = $this->faker->word());

        $this->cache->put('conscribo.session_id', 'testing');
        $this->cache->put('conscribo.entity_types', EntityTypeCollection::apiMake([
            ['typeName' => $resourceName],
        ]));

        $client = $this->getClient();

        $userQuery = $client->userQuery();

        $this->assertIsObject($userQuery);
        $this->assertEquals($resourceName, $userQuery->getResourceName());
    }

    public function test_query_creation_queries_objects(): void
    {
        $this->cache->put('conscribo.session_id', 'testing');

        Http::fakeSequence('https://secure.example.com/testing/request.json')
            ->pushFile(test_resource('http/conscribo/query/types_0.json'))
            ->pushFile(test_resource('http/conscribo/query/types_user_0.json'));

        $this->assertNull($this->cache->get('conscribo.entity_types'));

        $client = $this->getClient();

        $query = $client->query('user');

        Http::assertSentCount(2);

        $this->assertIsObject($query);

        /** @var EntityTypeCollection */
        $cachedTypes = $this->cache->get('conscribo.entity_types');
        $this->assertNotNull($cachedTypes);

        $this->assertCount(1, $cachedTypes);
        $this->assertTrue($cachedTypes->has('user'));

        $userType = $cachedTypes->get('user');
        $this->assertEquals('user', $userType->typeName);
        $this->assertCount(4, $userType->fields);
    }

    public function test_query_creation_from_cache(): void
    {
        $this->cache->put('conscribo.session_id', 'testing');
        $this->cache->put('conscribo.entity_types', EntityTypeCollection::apiMake([
            ['typeName' => 'user'],
        ]));

        $client = $this->getClient();

        $query = $client->query('user');

        Http::assertNothingSent();

        $this->assertIsObject($query);
    }

    public function test_query_creation_with_invalid_resource(): void
    {
        $this->cache->put('conscribo.session_id', 'testing');
        $this->cache->put('conscribo.entity_types', EntityTypeCollection::apiMake([
            ['typeName' => 'user'],
        ]));

        $client = $this->getClient();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid resource: devices');
        $client->query('devices');
    }
}
