<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\BotQuote;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BotQuoteControllerTest extends TestCase
{
    /**
     * @before
     */
    public function disableRateLimitOnBook(): void
    {
        $this->afterApplicationCreated(function () {
            RateLimiter::for('api-expensive', fn () => Limit::none());
        });
    }

    /**
     * Ensure authentication is prompted.
     */
    public function test_authentication(): void
    {
        [$user1, $user2, $user3] = User::factory(3)->create();

        $this->getJson(route('api.quotes.list'))
            ->assertUnauthorized();
        $this->getJson(route('api.quotes.list-all'))
            ->assertUnauthorized();
        $this->getJson(route('api.quotes.book'))
            ->assertUnauthorized();

        Sanctum::actingAs($user1);

        $this->getJson(route('api.quotes.list'))
            ->assertForbidden();
        $this->getJson(route('api.quotes.list-all'))
            ->assertForbidden();
        $this->getJson(route('api.quotes.book'))
            ->assertForbidden();

        $user2->assignRole('member');
        Sanctum::actingAs($user2);

        $this->getJson(route('api.quotes.list'))
            ->assertOk();
        $this->getJson(route('api.quotes.list-all'))
            ->assertForbidden();
        $this->getJson(route('api.quotes.book'))
            ->assertForbidden();

        $user3->assignRole('member');
        $user3->givePermissionTo('quotes-export');
        Sanctum::actingAs($user3);

        $this->getJson(route('api.quotes.list'))
            ->assertOk();
        $this->getJson(route('api.quotes.list-all'))
            ->assertOk();
        $this->getJson(route('api.quotes.book'))
            ->assertOk();
    }

    /**
     * Test retrieving the quotes as a normal member.
     */
    public function test_list_own_quotes(): void
    {
        [$user, $user2] = User::factory(2)->withRole('member')->create();

        Sanctum::actingAs($user);

        $userSentQuotes = BotQuote::factory(3)->for($user)->sent()->create();
        $otherUserQuotes = BotQuote::factory(3)->for($user2)->sent()->create();
        $userUnsentQuotes = BotQuote::factory(3)->for($user)->create();

        $responseJson = $this->getJson(route('api.quotes.list'))
            ->assertOk()
            ->json();

        $ids = Arr::pluck($responseJson['data'], 'id');

        $userSentQuotes->each(fn ($quote) => $this->assertContains($quote->id, $ids));
        $otherUserQuotes->each(fn ($quote) => $this->assertNotContains($quote->id, $ids));
        $userUnsentQuotes->each(fn ($quote) => $this->assertNotContains($quote->id, $ids));
    }

    /**
     * Ensure just the last month of quotes is returned when requesting own quotes.
     */
    public function test_list_own_timeframing(): void
    {
        $user = User::factory()->withRole('member')->create();

        [$now, $oneWeek, $twoWeek, $oneMonthInside, $oneMonthOutside, $twoMonth] = BotQuote::factory()->for($user)->sent()->createMany([
            ['created_at' => Date::now()],
            ['created_at' => Date::now()->subWeek(1)],
            ['created_at' => Date::now()->subWeek(2)],
            ['created_at' => Date::now()->subMonth()->addHour()],
            ['created_at' => Date::now()->subMonth()->subHour()],
            ['created_at' => Date::now()->subMonth(2)],
        ]);

        Sanctum::actingAs($user);

        $responseJson = $this->getJson(route('api.quotes.list'))
            ->assertOk()
            ->json();

        $ids = Arr::pluck($responseJson['data'], 'id');

        $this->assertContains($now->id, $ids);
        $this->assertContains($oneWeek->id, $ids);
        $this->assertContains($twoWeek->id, $ids);
        $this->assertContains($oneMonthInside->id, $ids);
        $this->assertNotContains($oneMonthOutside->id, $ids);
        $this->assertNotContains($twoMonth->id, $ids);
    }

    public function test_list_all(): void
    {
        [$user1, $user2, $user3] = User::factory(3)->create();
        $user1->assignRole('member', 'board');

        $userOneQuotes = BotQuote::factory(3)->for($user1)->sent()->create();
        $userTwoQuotes = BotQuote::factory(3)->for($user2)->sent()->create();
        $userThreeQuotes = BotQuote::factory(3)->for($user3)->sent()->create();

        $quotesFromLastYear = BotQuote::factory(3)->for($user2)->sent()->create([
            'created_at' => Date::now()->subYear(),
        ]);

        Sanctum::actingAs($user1);

        $responseJson = $this->getJson(route('api.quotes.list-all'))
            ->assertOk()
            ->json();

        $ids = Arr::pluck($responseJson['data'], 'id');

        $userOneQuotes->each(fn ($quote) => $this->assertContains($quote->id, $ids));
        $userTwoQuotes->each(fn ($quote) => $this->assertContains($quote->id, $ids));
        $userThreeQuotes->each(fn ($quote) => $this->assertContains($quote->id, $ids));

        $quotesFromLastYear->each(fn ($quote) => $this->assertNotContains($quote->id, $ids));
    }

    /**
     * Test the helper used to generate the "Book of St Nicholas.
     */
    public function test_book_helper(): void
    {
        Date::setTestNow('2023-12-01 00:00:00');

        $user = User::factory()->withRole(['member', 'board'])->create();

        $inRangeDates = [
            'December  5, 2022',
            'December 31, 2022',
            'January  1, 2023',
            'April 15, 2023',
            'August 20, 2023',
            'December  1, 2023',
            'December  5, 2023',
        ];

        $outOfRangeDates = [
            'December  1, 2022',
        ];

        $quoteFactory = BotQuote::factory()->forUser()->sent();
        $makeWithDate = fn ($date) => $quoteFactory->create(['created_at' => Date::parse("{$date} 12:00:00")]);

        $inRangeQuotes = Collection::make($inRangeDates)->map($makeWithDate);
        $outOfRangeQuotes = Collection::make($outOfRangeDates)->map($makeWithDate);

        Sanctum::actingAs($user);

        $responseJson = $this->getJson(route('api.quotes.book'))
            ->assertOk()
            ->json();

        $ids = Arr::pluck($responseJson['data'], 'id');

        $inRangeQuotes->each(fn ($quote) => $this->assertContains($quote->id, $ids));
        $outOfRangeQuotes->each(fn ($quote) => $this->assertNotContains($quote->id, $ids));
    }
}
