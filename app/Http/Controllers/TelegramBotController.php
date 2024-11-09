<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\Bots\HandleUpdateJob;
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
        // Parse an update
        $update = Telegram::getWebhookUpdate(true);

        // Dispatch the handler
        HandleUpdateJob::dispatchSync($update);

        // Respond accordingly
        return ResponseFacade::noContent();
    }
}
