<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Jobs\Bots\HandleUpdateJob;
use App\Models\Telegram\Chat;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Chat as BotChat;
use Telegram\Bot\Objects\Update as BotUpdate;

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

        try {
            $this->saveChatData($update);
        } catch (\Throwable $exception) {
            Log::warning("Failed storing chat information!", ['exception' => $exception]);
        }

        // Dispatch the handler
        HandleUpdateJob::dispatchSync($update);

        // Respond accordingly
        return ResponseFacade::noContent();
    }

    private function saveChatData(BotUpdate $update): void
    {
        $chat = object_get($update, 'message.chat');
        if (!$chat instanceof BotChat)
            return;

        // Check variables
        $chatId = (string)$chat->id;
        $chatType = $chat->type;
        $chatName = $chatType == 'private'
            ? Str::trim("{$chat->firstName} {$chat->lastName}")
            : $chat->title;

        // Save the data
        Chat::forChat($chatId)->fill([
            'type' => object_get($update, 'message.chat.type'),
            'name' => Str::limit($chatName, 120),
        ])->save();
    }
}
