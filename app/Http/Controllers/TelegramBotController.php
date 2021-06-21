<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    /**
     * Require signed requests.
     */
    public function __construct()
    {
        $this->middleware('signed');
    }

    /**
     * Handles requests from Telegram.
     */
    public function handle(): Response
    {
        // Handle command
        Telegram::commandsHandler(true);

        // Respond accordingly
        return ResponseFacade::make(Response::HTTP_NO_CONTENT);
    }
}
