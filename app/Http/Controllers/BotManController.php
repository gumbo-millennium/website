<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Providers\RouteServiceProvider;
use BotMan\BotMan\BotMan;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        // Register routes
        RouteServiceProvider::mapBotManCommands();

        // Now let BotMan handle it
        $botman = app('botman');
        \assert($botman instanceof BotMan);
        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('content.botman-tinker');
    }
}
