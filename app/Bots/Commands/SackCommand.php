<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Helpers\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;
use Telegram\Bot\Actions;

/**
 * @codeCoverageIgnore
 */
class SackCommand extends Command
{
    /**
     * The name of the Telegram command.
     */
    protected string $name = 'royatieverzoek';

    /**
     * The Telegram command description.
     */
    protected string $description = 'Stuurt iemand De Laan uit';

    /**
     * Command Argument Pattern.
     */
    protected string $pattern = '[^\s].+';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Get user and check member rights
        $user = $this->getUser();
        if (! $this->ensureIsMember($user)) {
            return;
        }

        // Rate limit early, to prevent chat spam.
        if ($this->rateLimit('sack', '🚷 Je mag nog geen nieuw royatieverzoek doen.', 'PT15M')) {
            return;
        }

        // Send upload status
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Check the quote
        $target = ucwords(trim($this->getCommandBody() ?? ''));
        $gifToUse = 'fired';

        // Allow self-firing
        if (empty($target)) {
            $target = $user->name;
            $gifToUse = 'fired-self';
        }

        // Get random lines
        $format = sprintf(
            '😡 %s dient een royatieverzoek in voor %s.',
            $user->name,
            $target,
        );

        // Write the file away
        try {
            Storage::append('sack.tsv', sprintf("%s\t%s\t%s\n", date('Y-m-d H:i:s'), Str::ascii($user->name), Str::ascii($target)));
        } catch (FilesystemException) {
            Log::warning('Could not write sack.txt file for {user} sacking {target}', [
                'user' => $user->name,
                'target' => $target,
            ]);
        }

        // Send as-is, but with a gif
        if ($gif = $this->getReplyGifUrl($gifToUse)) {
            $this->replyWithAnimation([
                'animation' => $gif,
            ]);
        }

        $this->replyWithMessage([
            'text' => $format,
        ]);
    }
}
