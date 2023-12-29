<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Conscribo\Concerns;

use App\Services\Conscribo\Concerns\MapsEntityResults;
use App\Services\Conscribo\Data\EntityFieldCollection;
use App\Services\Conscribo\Enums\FieldType;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MapsEntityResultsTest extends TestCase
{
    use MapsEntityResults;
    use WithFaker;

    public static function provideTestMappings(): array
    {
        return [
            'string' => [FieldType::Text, 'test', 'test'],
            'number (float)' => [FieldType::Number, '1.0', 1.0],
            'number (float, no decimals)' => [FieldType::Number, '1', 1.0],
            'integer' => [FieldType::Integer, '1234', 1234],
            'amount' => [FieldType::Amount, '12,34', Money::ofMinor(1234, 'EUR')],
            'checkbox (boolean)' => [FieldType::Checkbox, '1', true],
        ];
    }

    /**
     * @dataProvider provideTestMappings
     */
    public function test_mapping(FieldType $type, string $inputValue, mixed $expectedValue): void
    {
        $types = EntityFieldCollection::apiMake([
            [
                'fieldName' => 'test',
                'type' => $type->value,
            ],
        ]);

        $mappedValue = $this->mapEntityResults($types, [
            ['test' => $inputValue],
        ]);

        $this->assertEquals($expectedValue, $mappedValue->first()->test);
    }
}
