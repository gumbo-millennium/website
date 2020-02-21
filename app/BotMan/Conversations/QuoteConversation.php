<?php

declare(strict_types=1);

namespace App\BotMan\Conversations;

use App\Helpers\Arr;
use App\Models\BotQuote;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\Drivers\Telegram\TelegramDriver;
use Str;

class QuoteConversation extends InvokableConversation
{
    private const STR_THANKS = [
        'Bedankt voor het insturen van je wist-je-datje, :name!',
        'Whoop, lekker bezig :name, ik stuur \'m door naar de Gumbode',
        'Bleep bloop, opgeslagen onder "Chantagemateriaal van :name".',
    ];

    private const STR_ASK = [
        'Nee :name, zo werkt dat niet. Wat wou je insturen?',
        'Ik ga niet wéér een lege quote opslaan :name, wat is je wist-je-datje?',
        'Bleep bloop, geeef bericht',
        '[Michael Nicht] Klootviool, wat doe je?'
    ];

    /**
     * First question
     */
    public function askForQuote()
    {
        // Get bot
        $bot = $this->getBot();
        $message = $bot->getMessage();

        // Prep extra params
        $extraParams = [];

        if ($bot->getDriver() instanceof TelegramDriver && $message->getSender() !== $message->getRecipient()) {
            $extraParams['reply_to_message_id'] = Arr::get($message->getPayload(), 'message_id');
            $extraParams['reply_markup'] = json_encode([
                'force_reply' => true,
                'selective' => true,
            ]);
        }

        $prompt = \str_replace(':name', $this->getName(), Arr::random(self::STR_ASK));
        $this->ask($prompt, function (Answer $response) {
            $this->storeQuote($response->getText());
        }, $extraParams);
    }

    protected function storeQuote(string $quote)
    {
        // Get bot
        $bot = $this->getBot();

        // Start typing
        $bot->types(2);

        // Save the quote
        $quote = BotQuote::create([
            'user_id' => optional($this->getUser())->id,
            'display_name' => $this->getName(),
            'quote' => $quote,
        ]);

        $this->say(str_replace(':name', $this->getName(), Arr::random(self::STR_THANKS)));
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        // Check for quote
        $bot = $this->getBot();
        $message = $bot->getMessage();
        $messageText = $message->getText();
        $quote = trim(Str::after($messageText, '/wjd'));

        if (preg_match("/^(.*)\@(?:[a-z0-9-_\.]+)$/", $quote, $matches)) {
            $quote = trim($matches[1]);
        }

        if (empty($quote)) {
            $this->askForQuote();
            return;
        }

        $this->storeQuote($quote);
    }
}
