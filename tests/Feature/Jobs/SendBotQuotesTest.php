<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SendBotQuotes;
use App\Mail\BotQuotesMessage;
use App\Models\BotQuote;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendBotQuotesTest extends TestCase
{
    /**
     * @before
     */
    public function setupMailFakeAndCreateUser(): void
    {
        $this->afterApplicationCreated(function () {
            Mail::fake();
            User::factory()->createQuietly();
        });
    }

    public function test_job_skips_if_no_quotes_are_to_be_sent(): void
    {
        BotQuote::factory()->times(10)->create(['submitted_at' => Date::now()]);

        Config::set('gumbo.quote-to', 'test@example.com');

        SendBotQuotes::dispatchSync();

        Mail::assertNothingOutgoing();
    }

    public function test_job_skips_if_no_recipient_mailbox_is_set(): void
    {
        BotQuote::factory()->times(10)->create();

        Config::set('gumbo.quote-to', null);

        SendBotQuotes::dispatchSync();

        Mail::assertNothingOutgoing();
    }

    public function test_happy_trail_on_job(): void
    {
        $quotes = BotQuote::factory()->times(10)->create(['updated_at' => Date::now()]);
        $sentQuotes = BotQuote::factory()->times(5)->create(['submitted_at' => Date::now()]);

        $this->travel(5)->days();

        Config::set('gumbo.quote-to', 'test@example.com');

        SendBotQuotes::dispatchSync();

        Mail::assertSent(BotQuotesMessage::class);

        foreach ($quotes as $quote) {
            $freshQuote = $quote->fresh();
            $this->assertTrue($quote->updated_at->isBefore($freshQuote->updated_at), 'Quote updated_at should be before fresh quote updated_at');
            $this->assertNull($quote->submitted_at);
            $this->assertNotNull($freshQuote->submitted_at);
        }

        foreach ($sentQuotes as $quote) {
            $freshQuote = $quote->fresh();
            $this->assertEquals($quote->updated_at, $freshQuote->updated_at);
            $this->assertEquals($quote->submitted_at, $freshQuote->submitted_at);
        }
    }
}
