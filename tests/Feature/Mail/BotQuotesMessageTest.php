<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Helpers\Str;
use App\Mail\BotQuotesMessage;
use App\Models\BotQuote;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class BotQuotesMessageTest extends TestCase
{
    public static function provideQuoteDates(): array
    {
        return [
            'within one week' => [
                ['2023-01-03', '2023-01-06'],
                'gumbo.bot-quotes.titles.week',
                ['week1' => 1, 'year1' => 2023],
            ],
            'within two weeks' => [
                ['2023-01-01', '2023-01-11'],
                'gumbo.bot-quotes.titles.month',
                ['month1' => 'januari', 'year1' => 2023],
            ],
            'within one month' => [
                ['2023-01-01', '2023-01-31'],
                'gumbo.bot-quotes.titles.month',
                ['month1' => 'januari', 'year1' => 2023],
            ],
            'within two months' => [
                ['2023-01-01', '2023-01-30', '2023-02-16'],
                'gumbo.bot-quotes.titles.adjacent-months',
                ['month1' => 'januari', 'year1' => 2023, 'month2' => 'februari', 'year2' => 2023],
            ],
            'spanning three months' => [
                ['2023-01-01', '2023-01-30', '2023-02-16', '2023-03-01'],
                'gumbo.bot-quotes.titles.spanning-months',
                ['month1' => 'januari', 'year1' => 2023, 'month2' => 'maart', 'year2' => 2023],
            ],
            'spanning two adjacent months in two years' => [
                ['2022-12-01', '2022-12-30', '2023-01-16', '2023-01-31'],
                'gumbo.bot-quotes.titles.adjacent-months',
                ['month1' => 'december', 'year1' => 2022, 'month2' => 'januari', 'year2' => 2023],
            ],
            'spanning four months in two years' => [
                ['2022-11-01', '2022-12-30', '2023-01-16', '2023-02-01'],
                'gumbo.bot-quotes.titles.spanning-months',
                ['month1' => 'november', 'year1' => 2022, 'month2' => 'februari', 'year2' => 2023],
            ],
        ];
    }

    /**
     * @before
     */
    public function createUserBeforeTest(): void
    {
        $this->afterApplicationCreated(fn () => User::factory()->createQuietly());
    }

    /**
     * @dataProvider provideQuoteDates
     * @param string[] $quoteDates List of dates
     */
    public function test_mail_subject(array $quoteDates, string $expectedTranslationKey, array $translationArgs): void
    {
        $quotes = BotQuote::factory()
            ->createMany(collect($quoteDates)->map(fn (string $date) => ['created_at' => Date::parse($date)]))
            ->each->refresh();

        $message = new BotQuotesMessage($quotes);

        $translatedSubject = __($expectedTranslationKey, $translationArgs);

        $this->assertSame($translatedSubject, $message->build()->subject);
    }

    public function test_email_contains_all_quotes(): void
    {
        $quotes = BotQuote::factory()->times(10)->create();

        $message = new BotQuotesMessage($quotes);

        $body = $message->render();

        foreach ($quotes as $quote) {
            $this->assertStringContainsString($quote->quote, $body);
        }
    }

    public function test_email_has_a_single_excel_attachment(): void
    {
        $quotes = BotQuote::factory()->times(10)->create();

        $message = new BotQuotesMessage($quotes);
        $builtMessage = $message->build();

        $attachments = $builtMessage->rawAttachments;
        $subject = $builtMessage->subject;

        $this->assertCount(1, $attachments);

        $firstAttachment = head($attachments);
        $this->assertStringContainsString(Str::slug($subject), $firstAttachment['name']);
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $firstAttachment['options']['mime']);
    }
}
