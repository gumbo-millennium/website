<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\BotQuote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\FeatureTestCase;

class BotQuoteControllerTest extends FeatureTestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Tests the permissions, should be open for everyone.
     */
    public function test_page_display(): void
    {
        $response = $this->get(route('account.quotes'));
        $response->assertRedirect(route('login'));

        $this->actingAs($this->getGuestUser());
        $response = $this->get(route('account.quotes'));
        $response->assertOk();

        $this->actingAs($this->getMemberUser());
        $response = $this->get(route('account.quotes'));
        $response->assertOk();
    }

    /**
     * Tests if the deletion is shown for the quotes only.
     */
    public function test_delete_button_appearance(): void
    {
        $user = $this->getGuestUser();
        $this->actingAs($user);

        $newQuotes = BotQuote::factory()
            ->times(8)
            ->for($user)
            ->create();

        $sentQuotes = BotQuote::factory()
            ->times(6)
            ->sent()
            ->for($user)
            ->create();

        $response = $this->get(route('account.quotes'))
            ->assertOk();

        // Count the number of delete buttons
        foreach ($newQuotes as $quote) {
            $response->assertSee("data-delete-quote-id=\"{$quote->id}\"", false);
        }

        foreach ($sentQuotes as $quote) {
            $response->assertDontSee("data-delete-quote-id=\"{$quote->id}\"", false);
        }
    }

    public function test_date_labels(): void
    {
        $user = $this->getGuestUser();
        $this->actingAs($user);

        $createDated = fn (string $date) => [
            'user_id' => $user->id,
            'created_at' => $this->faker->dateTimeBetween("{$date}T04:00:00", "{$date}T22:00:00"),
        ];

        BotQuote::factory()
            ->createMany([
                $createDated('2021-01-12'),
                $createDated('2021-01-12'),
                $createDated('2021-01-12'),

                $createDated('2021-01-15'),
                $createDated('2021-01-15'),

                $createDated('2021-01-18'),
                $createDated('2021-01-22'),
            ]);

        // Get quotes
        $response = $this->get(route('account.quotes'));
        $response->assertOk();

        // Get contents
        $contents = (string) $response->getContent();

        $res = preg_match_all('/(?<date>(?:[a-z]+dag) (?:\d+)) jan/', $contents, $matches);
        $this->assertEquals(4, $res, 'Date string count doesn\'t match.');

        $this->assertEquals([
            'vrijdag 22',
            'maandag 18',
            'vrijdag 15',
            'dinsdag 12',
        ], $matches['date']);
    }

    /**
     * Deleting unsent quotes should be fine.
     */
    public function test_delete_unsent_quote(): void
    {
        $user = $this->getGuestUser();
        $this->actingAs($user);

        // Test unsent quotes to self
        $unsentSelf = BotQuote::factory()
            ->create(['user_id' => $user->id]);

        $response = $this
            ->delete(route('account.quotes.delete'), ['quote-id' => $unsentSelf->id]);

        // Check result
        $response->assertRedirect(route('account.quotes'));
        $this->assertFlashMessageContains('Wist-je-datje verwijderd');
        $this->assertNull(BotQuote::find($unsentSelf->id));
    }

    /**
     * Sent quotes should not be delete-able.
     */
    public function test_delete_sent_quote(): void
    {
        $user = $this->getGuestUser();
        $this->actingAs($user);

        // Test sent quotes to self
        $sentSelf = BotQuote::factory()
            ->sent()
            ->create(['user_id' => $user->id]);

        $response = $this
            ->delete(route('account.quotes.delete'), ['quote-id' => $sentSelf->id]);

        // Check result
        $response->assertRedirect(route('account.quotes'));
        $this->assertFlashMessageContains('is al verzonden');
        $this->assertNotNull(BotQuote::find($sentSelf->id));
    }

    /**
     * You shouldn't be able to delete quotes of others, even if unsent.
     */
    public function test_delete_unsent_unowned_quote(): void
    {
        $user = $this->getGuestUser();
        $user2 = $this->getGuestUser();
        $this->actingAs($user);

        // Test an unsent quote from User 2
        $unsentQuote = BotQuote::factory()
            ->create(['user_id' => $user2->id]);

        $response = $this
            ->delete(route('account.quotes.delete'), ['quote-id' => $unsentQuote->id]);
        $response->assertNotFound();

        // Check result
        $response->assertNotFound();
        $this->assertNotNull(BotQuote::find($unsentQuote->id));

        // Test a sent quote from User 2
        $sentQuote = BotQuote::factory()
            ->sent()
            ->create(['user_id' => $user2->id]);

        $response = $this
            ->delete(route('account.quotes.delete'), ['quote-id' => $sentQuote->id]);

        $response->assertNotFound();

        // Check result
        $response->assertNotFound();
        $this->assertNotNull(BotQuote::find($sentQuote->id));
    }

    /**
     * You shouldn't be able to delete quotes of others.
     */
    public function test_delete_sent_unowned_quote(): void
    {
        $user = $this->getGuestUser();
        $user2 = $this->getGuestUser();
        $this->actingAs($user);

        // Test sent from anon
        $sentAnon = BotQuote::factory()->sent()->create(['user_id' => $user2->id]);
        $response = $this->delete(route('account.quotes.delete'), ['quote-id' => $sentAnon->id]);
        $response->assertNotFound();

        // Check result
        $response->assertNotFound();
        $this->assertNotNull(BotQuote::find($sentAnon->id));
    }
}
