<?php

namespace App\Console\Commands\Telegram;

use App\Models\Telegram\Chat;
use Illuminate\Console\Command;

class ListChatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:telegram:chats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all chats the bot has interacted with.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Chats
        $chats = Chat::query()
            ->select(['chat_id', 'type', 'name'])
            ->orderBy('chat_id')
            ->lazy();

        // Print
        $this->table(
            ['Chat ID', 'type', 'name'],
            $chats
        );
    }
}
