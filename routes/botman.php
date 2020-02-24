<?php

declare(strict_types=1);

use App\BotMan\Conversations\QuoteConversation;
use App\BotMan\Messages\ActivitiesCommand;
use App\BotMan\Messages\FallbackMessage;
use App\BotMan\Messages\HelpMessage;
use App\BotMan\Messages\PlazaCamMessage;
use App\BotMan\Messages\StartMessage;
use App\BotMan\Middleware\LogsReceives;
use App\BotMan\Middleware\LogsSends;
use App\BotMan\Middleware\TelegramMiddleware;
use BotMan\BotMan\BotMan;

$botman = resolve('botman');
\assert($botman instanceof BotMan);

// Add middlewares
$botman->middleware->received(new LogsReceives(), new TelegramMiddleware());
$botman->middleware->sending(new LogsSends(), new TelegramMiddleware());

// Generic commands
$botman->hears('/activiteiten', ActivitiesCommand::class);
$botman->hears('/help', HelpMessage::class);
$botman->hears('/start', StartMessage::class);

// Member-only commands
$botman->hears('/koffiecam', PlazaCamMessage::class);
$botman->hears('/plazacam', PlazaCamMessage::class);
$botman->hears('/wjd {text}', QuoteConversation::class);
$botman->hears('/wjd', QuoteConversation::class);

// Fallback
$botman->fallback(FallbackMessage::class);
