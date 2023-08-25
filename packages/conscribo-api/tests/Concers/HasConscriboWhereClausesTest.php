<?php

declare(strict_types=1);

namespace Tests\Gumbo\ConscriboApi\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Mockery;
use Tests\Gumbo\ConscriboApi\fixtures\MockWithConscriboWhereClauses;
use Tests\Gumbo\ConscriboApi\TestCase;

class HasConscriboWhereClausesTest extends TestCase
{
    public static function provideWhereMethodToOperatorMapping(): array
    {
        $minDate = Carbon::parse('2023-01-01');
        $maxDate = Carbon::parse('2023-12-31');

        return [
            'where contains' => ['whereContains', '~', 'test'],
            'where not contains' => ['whereNotContains', '!~', 'test'],
            'where starts with' => ['whereStartsWith', '|=', 'test'],
            'where not empty' => ['whereNotEmpty', '+'],
            'where empty' => ['whereEmpty', '-'],
            'where date is between' => ['whereDateIsBetween', '><', [$minDate, $maxDate], ['start' => $minDate, 'stop' => $maxDate]],
            'where date is after' => ['whereDateIsAfter', '>=', [$minDate], ['start' => $minDate]],
            'where date is before' => ['whereDateIsBefore', '<=', [$maxDate], ['stop' => $maxDate]],
            'where number is between' => ['whereNumberIsBetween', '=', [1, 10], '>1&<10'],
            'where number is greater than' => ['whereNumberIsGreaterThan', '=', 1, '>1'],
            'where number is less than' => ['whereNumberIsLessThan', '=', 10, '<10'],
            'where multicheckbox is in' => ['whereMulticheckboxIn', 'in', 1 & 2 & 4],
            'where multicheckbox is all in' => ['whereMulticheckboxAllIn', 'all', 1 & 2 & 4],
            'where multicheckbox is not in' => ['whereMulticheckboxNotIn', '<>', 1 & 2 & 4],
        ];
    }

    /**
     * @dataProvider provideWhereMethodToOperatorMapping
     *
     * @covers \Gumbo\ConscriboApi\Concerns\HasConscriboWhereClauses
     */
    public function test_where_forwarding_constructs(string $methodName, string $operator, $value = null, $expectedValue = null): void
    {
        $mock = Mockery::mock(MockWithConscriboWhereClauses::class)->makePartial();

        $mockMethod = $mock->expects()
            ->where('field', $operator, $expectedValue ?? $value ?? null)
            ->once()
            ->andReturnSelf();

        $mock->{$methodName}('field', ...Arr::wrap($value));

        $mockMethod->verify();
    }
}
