<?php

declare(strict_types=1);

namespace App\Jobs\Bots;

use App\Models\Telegram\Chat;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Chat as TelegramChat;
use Telegram\Bot\Objects\Update;

class HandleUpdateJob
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly Update $update)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Handle save
        $this->saveChatData();

        // Handle reaction
        if ($this->update->isType('message_reaction')) {
            HandleUpdatedReactionJob::dispatchSync($this->update);
        }

        // Handle message
        if ($this->update->isType('message')) {
            Telegram::bot()->processCommand($this->update);
        }
    }

    private function saveChatData(): void
    {
        $chat = object_get($this->update, 'message.chat');
        if (!$chat instanceof TelegramChat)
            return;

        // Check variables
        $chatId = (string)$chat->id;
        $chatType = $chat->type;
        $chatName = $chatType == 'private'
            ? trim("{$chat->firstName} {$chat->lastName}")
            : $chat->title;

        // Save the data
        Chat::forChat($chatId)->fill([
            'type' => $chatType,
            'name' => Str::limit($chatName, 120),
        ])->save();
    }
}
