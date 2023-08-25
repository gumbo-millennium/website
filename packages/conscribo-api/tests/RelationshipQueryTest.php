<?php

declare(strict_types=1);

namespace Tests\Gumbo\ConscriboApi;

use Carbon\Carbon;
use Gumbo\ConscriboApi\ConscriboApiClient as ConscriboApiClientImpl;
use Gumbo\ConscriboApi\Contracts\ConscriboApiClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class RelationshipQueryTest extends TestCase
{
    private array $fieldTypes;

    /**
     * @var ConscriboApiClient|\Mockery\MockInterface
     */
    private ConscriboApiClient $client;

    /**
     * @before
     */
    public function loadPersoonFieldTypesAndMockApiClient(): void
    {
        $this->afterApplicationCreated(function () {
            $contents = Arr::get(Yaml::parseFile(__DIR__.'/fixtures/fields/persoon.yaml'), 'fields');

            $this->fieldTypes = Collection::make($contents)
                ->mapWithKeys(fn ($row) => [$row['fieldName'] => $row['type']])
                ->all();

            $client = $this->client = mock(ConscriboApiClientImpl::class)->makePartial();

            $client->allows()->getEntityTypes()->andReturn([
                'persoon' => $this->fieldTypes,
            ]);

            $this->app->instance(ConscriboApiClient::class, $client);
        });
    }

    /**
     * @dataProvider provideValidWhereOperators
     *
     * @covers \Gumbo\ConscriboApi\RelationQuery::where
     * @covers \Gumbo\ConscriboApi\RelationQuery::validateWhereOnKey
     */
    public function test_valid_where_quries(string $key, $operator, $value = null, $impliedOperator = null): void
    {
        $repo = $this->client->resource('persoon');

        $repo->where($key, $operator, $value);

        $filters = $repo->getFilters();

        $this->assertNotEmpty($filters);
        $firstKey = head($filters);
        $this->assertSame($key, $firstKey['key']);

        if ($impliedOperator) {
            $this->assertSame($impliedOperator, $firstKey['operator']);
        }
    }

    /**
     * @covers \Gumbo\ConscriboApi\RelationQuery::where
     */
    public function test_happy_flow()
    {
        $mockMethod = $this->client->expects()
            ->makeApiCall('listRelations', [
                'requestedFields' => array_keys($this->fieldTypes),
                'filters' => [
                    ['key' => 'naam', 'operator' => '=', 'value' => 'John Doe'],
                    ['key' => 'email', 'operator' => '=', 'value' => 'john.doe@example.com'],
                ],
            ])
            ->andReturn([
                'resultCount' => 0,
                'Relations' => [],
            ])
            ->once();

        $repo = $this->client->resource('persoon');

        $repo->where('naam', '=', 'John Doe')
            ->where('email', '=', 'john.doe@example.com');

        $filters = $repo->getFilters();
        $this->assertCount(2, $filters);
        $this->assertEquals(['naam', 'email'], Arr::pluck($filters, 'key'));

        $result = $repo->get();

        $mockMethod->verify();

        $this->assertIsIterable($result);
        $this->assertCount(0, $result);
    }

    public static function provideValidWhereOperators(): array
    {
        $stringLikeFields = [
            ['string', 'naam'],
            ['email', 'email'],
            ['account', 'rekening'],
        ];

        $tests = [];
        foreach ($stringLikeFields as [$type, $field]) {
            $tests[] = [
                "$type equals" => [$field, '=', 'John Doe'],
                "$type contains" => [$field, '~', 'John Doe'],
                "$type does not  contain" => [$field, '|~', 'John Doe'],
                "$type starts with" => [$field, '|=', 'John Doe'],
                "$type is not empty" => [$field, '+', ''],
                "$type is empty" => [$field, '-', ''],
            ];
        }

        $minDate = Carbon::parse('2023-01-01');
        $maxDate = Carbon::parse('2023-12-31');

        $tests[] = [
            'date equals' => ['geboortedatum', '=', $minDate],
            'date between' => ['geboortedatum', '><', [$minDate, $maxDate]],
            'date greater than' => ['geboortedatum', '>=', $minDate],
            'date less than' => ['geboortedatum', '<=', $maxDate],
        ];

        $tests[] = [
            'number equals' => ['telefoonnummer', '=', '612345678'],
            'number greater than' => ['telefoonnummer', '=', '>60'],
            'number less than' => ['telefoonnummer', '=', '<60'],
            'number between' => ['telefoonnummer', '=', '>60&<70'],
            'number outside' => ['telefoonnummer', '=', '<60|>70'],
            'number exact or higher' => ['telefoonnummer', '=', '30|>60'],
        ];

        $tests[] = [
            'checkbox checked with bool' => ['achterstallig', '=', true],
            'checkbox checked with number' => ['achterstallig', '=', 1],
            'checkbox unchecked with bool' => ['achterstallig', '=', false],
            'checkbox unchecked with number' => ['achterstallig', '=', 0],
        ];

        $tests[] = [
            'enum equals' => ['aanhef', '=', 'Dhr.'],
        ];

        $allTests = Arr::collapse($tests);

        foreach ($allTests as $name => [$key, $value, $operator]) {
            if ($operator === '=') {
                $allTests["$name (implied)"] = [$key, $value, null, '='];
            }
        }

        return $allTests;
    }
}
