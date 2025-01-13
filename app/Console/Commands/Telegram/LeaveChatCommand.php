<?php

declare(strict_types=1);

namespace App\Console\Commands\Telegram;

use App\Models\Telegram\Chat;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

use function Laravel\Prompts\search;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Telegram\Bot\Api as TelegramApi;
use Telegram\Bot\FileUpload\InputFile;

class LeaveChatCommand extends Command implements PromptsForMissingInput
{
    private const LEAVABLE_CHAT_TYPES = ['group', 'supergroup'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        telegram:leave-chat-command
            {chat}
            {--all : Leave all chats}
            {--force : Don\'t ask for verification}
            {--dry-run : Pretend to leave}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Leave a chat, or all chats';

    /**
     * Execute the console command.
     */
    public function handle(TelegramApi $bot)
    {
        // Verify chat
        $chat = $this->findChat();
        if (! $this->option('all') && ! $chat) {
            $this->line("<fg=red>Failed to find chat with ID {$this->argument('chat')}.</>");

            return self::FAILURE;
        }

        $confirmPrompt = $chat
            ? "Are you sure you want to leave chat “{$chat->name}”?"
            : 'Are you sure you want to leave <fg=red>all</fg> chats?';

        if (! $this->option('force') && ! $this->confirm($confirmPrompt)) {
            $this->line('<fg=red>Failed to confirm.</>');

            return self::FAILURE;
        }

        $chat != null ? $this->leaveChat($bot, $chat) : $this->leaveAllChats($bot);
    }

    protected function leaveChat(TelegramApi $bot, Chat $chat): void
    {
        if (! in_array($chat->type, self::LEAVABLE_CHAT_TYPES, true)) {
            $this->line("<warn>Cannot leave a chat of type [{$chat->type}].");

            return;
        }

        $bot->sendAnimation([
            'chat_id' => $chat->chat_id,
            'animation' => InputFile::create(resource_path('assets/public/telegram-bot/goodbye-telegram.mp4'), 'goodbye-telegram.mp4'),
            'show_caption_above_media' => false,
            'caption' => <<<'TEXT'
                Het is tijd voor mij om te gaan.
                Bedankt voor al jullie spam.

                Liefs, de Gumbot 🤖
                TEXT,
        ]);

        if (! $this->option('dry-run')) {
            $bot->leaveChat([
                'chat_id' => $chat->chat_id,
            ]);
        }

        $chat->update(['left_at' => now()]);
    }

    protected function leaveAllChats(TelegramApi $bot): void
    {
        $chats = Chat::query()
            ->whereNull('chat_id')
            ->whereIn('type', self::LEAVABLE_CHAT_TYPES)
            ->all();

        foreach ($chats as $chat) {
            $this->leaveChat($bot, $chat);
        }
    }

    /**
     * Bypass asking for a chat when --all is given.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            $input->setArgument('chat', 'any');
        }

        parent::interact($input, $output);
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'chat' => fn () => search(
                label: 'Search for a chat:',
                options: fn ($value) => strlen($value) > 0
                    ? Chat::query()
                        ->where('name', 'like', "%{$value}%")
                        ->pluck('name', 'id')
                        ->all()
                    : [],
            ),
        ];
    }

    private function findChat(): ?Chat
    {
        $chatId = $this->argument('chat');

        return Chat::query()->where(
            fn ($query) => $query
                ->where('chat_id', $chatId)
                ->orWhere('chat_id', "-{$chatId}")
                ->orWhere('id', $chatId),
        )->first();
    }
}
