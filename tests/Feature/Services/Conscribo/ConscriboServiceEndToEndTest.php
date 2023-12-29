<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Conscribo;

use App\Services\Conscribo\Data\Entity;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

class ConscriboServiceEndToEndTest extends ConscriboTestCase
{
    public function test_cache_free_query_request(): void
    {
        $this->assertNull($this->cache->get('conscribo.session_id'));

        Http::fakeSequence('https://secure.example.com/testing/request.json')
            ->pushFile(test_resource('http/conscribo/e2e/query_0_auth_and_types.json'))
            ->pushFile(test_resource('http/conscribo/e2e/query_1_types_user.json'))
            ->pushFile(test_resource('http/conscribo/e2e/query_2_fetch_user.json'));

        $response = $this->getClient()->query('user')->execute();

        Http::assertSequencesAreEmpty();

        $this->assertNotNull($this->cache->get('conscribo.session_id'));
        $this->assertNotNull($this->cache->get('conscribo.entity_types'));

        $this->assertCount(2, $response);

        [$row1, $row2] = $response->values();

        $this->assertEquals(
            new Entity([
                'code' => '2',
                'selector' => '2: Sam Smith',
                'startdatum_lid' => Date::parse('2016-01-01'),
                'achterstallig_20_21' => false,
            ]),
            $row1,
        );

        $this->assertEquals(
            new Entity([
                'code' => '3',
                'selector' => '3: Alex Davies',
                'startdatum_lid' => Date::parse('2018-11-06'),
                'achterstallig_20_21' => true,
            ]),
            $row2,
        );
    }
}
