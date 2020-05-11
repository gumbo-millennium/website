<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\Models\User;
use BotMan\BotMan\BotMan;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DennisMessage extends AbstractMessage
{
    private const BEER_DISK = 'local';
    private const BEER_FILE = 'botman-dennisbier';

    /**
     * Sends a list of commands to the user
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function run(BotMan $bot, ?User $user): void
    {
        // Get a lock
        $lock = Cache::lock('botman.dennis', 2);

        try {
            $lock->block(10);
            // Got lock

            $this->sendDennisBier($bot);
        } catch (LockTimeoutException $exception) {
            // failed to get lock
            $bot->reply('Ik ben even de tel kwijt 😞');
        } finally {
            // Release lock
            $lock->release();
        }
    }

    private function sendDennisBier(BotMan $bot): void
    {
        // Get file
        try {
            $contents = Storage::disk(self::BEER_DISK)->get(self::BEER_FILE);
        } catch (FileNotFoundException $e) {
            $contents = null;
        }

        // Get count, make sure it's positive
        $count = filter_var($contents, \FILTER_VALIDATE_INT) ?? 0;
        $count = max(0, $count);

        // Raise the count
        $count++;

        // Write the new count
        Storage::disk(self::BEER_DISK)->put(self::BEER_FILE, sprintf('%d', $count));

        // Reply
        $bot->reply(sprintf('%s Dennis bier.', \number_format($count, 0, ',', '.')));
    }
}
