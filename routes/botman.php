<?php

declare(strict_types=1);

use App\BotMan\Conversations\QuoteConversation;
use App\BotMan\Messages\ActivitiesCommand;
use App\BotMan\Messages\PlazaCamMessage;
use App\BotMan\Middleware\LogsReceives;
use App\BotMan\Middleware\LogsSends;
use App\BotMan\Middleware\TelegramMiddleware;
use App\Helpers\Arr;
use BotMan\BotMan\BotMan;

$botman = resolve('botman');
\assert($botman instanceof BotMan);

// Add middlewares
$botman->middleware->received(new LogsReceives(), new TelegramMiddleware());
$botman->middleware->sending(new LogsSends(), new TelegramMiddleware());

// Member-only commands
$botman->hears('/activiteiten', ActivitiesCommand::class);
$botman->hears('/(plaza|koffie)cam', PlazaCamMessage::class);
$botman->hears('/wjd', QuoteConversation::class);
$botman->hears('/wjd {text}', QuoteConversation::class);

$botman->fallback(static function (BotMan $bot) {
    $message = $bot->getMessage();
    if (empty($message->getText()) || substr($message->getText(), 0, 1) !== '/') {
        return;
    }

    $masterMoviesFactor = random_int(1, 100);
    if ($masterMoviesFactor < 95) {
        $bot->reply('Sorry, dit commando ken ik niet.');
        return;
    }

    $quote = Arr::random([
        'Klootviool, wat doe je!?',
        'Waar zat je met je lul, toen je zei dat Born To Be Alive van The Village People was?',
        ['Hoe gaat \'ie met jou?', 900, 'Met mij best wel wauw.'],
        'Goedemiddag klootviool, met Michael Nicht.',
        ['Ik voel me een beetje opgeblazen...', 1500, 'Maar dat maakt niet uit, tijd voor een koekje.'],
        'Baas, ik voel me gebruikt.',
        'Heeft er ooit iemand *iets* bereikt met leren?'
    ]);

    if (!is_array($quote)) {
        $bot->reply($quote);
        return;
    }

    foreach ($quote as $line) {
        if (is_string($line)) {
            $bot->reply($line);
            continue;
        } elseif (is_int($line)) {
            $bot->typesAndWaits($line / 1000);
        }
    }
});
