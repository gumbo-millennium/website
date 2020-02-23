<?php

declare(strict_types=1);

namespace App\BotMan\Conversations;

use App\Helpers\Arr;
use App\Models\BotQuote;
use BotMan\BotMan\Messages\Incoming\Answer;

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
        // Ask the question
        $prompt = \str_replace(':name', $this->getName(), Arr::random(self::STR_ASK));

        // Ask it
        $this->ask($prompt, function (Answer $response) {
            $this->storeQuote($response->getText());
        }, [
            'auto-reply' => true
        ]);
    }

    protected function storeQuote(string $quote)
    {
        // Get bot and start typing
        $this->getBot()->types();

        // Save the quote
        $quote = BotQuote::create([
            'user_id' => optional($this->getUser())->id,
            'display_name' => $this->getName(),
            'quote' => $quote,
        ]);

        // Reply about it
        $this->say(str_replace(':name', $this->getName(), Arr::random(self::STR_THANKS)));
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        // Check for quote
        $bot = $this->getBot();
        $quote = Arr::get($bot->getMatches(), 'text');

        // Ask for the quote
        if (empty($quote)) {
            $this->askForQuote();
            return;
        }

        // Store the quote
        $this->storeQuote($quote);
    }
}
