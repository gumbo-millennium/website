<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\BotMan\Traits\HasGroupCheck;
use App\Models\User;
use BotMan\BotMan\BotMan;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DennisMessage extends AbstractMessage
{
    use HasGroupCheck;

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
        // Allow admin block
        if ($this->isInGroup()) {
            $bot->reply('Sorry, Dennisbieren kan alleen in privéchat');
            return;
        }

        // Check expire
        $userStore = $bot->userStorage();
        $expire = $userStore->get('dennis-expire') ?? 0;
        if ($expire > time()) {
            $warns = $userStore->get('dennis-warn') ?? 0;

            // Block above threshold
            if ($warns >= 10) {
                $bot->say('Sorry, je account is nu geblokkeerd. Probeer het later nog eens');
                $userStore->save(['banned' => now()->addDays(2)->getTimestamp()]);
                return;
            }

            // Reply with ETA
            $bot->reply(sprintf(
                'Rustig aan. je mag weer dennisbieren om %s.',
                Carbon::createFromTimestamp($expire)->format('H:i:s (T)')
            ));

            // Save new warning count
            $userStore->save([
                'dennis-warn' => ($warns + 1)
            ]);
            return;
        }

        // Store
        $userStore->save([
            'dennis-expire' => now()->addMinutes(2)->getTimestamp(),
            'dennis-warns' => 0
        ]);

        // Get a lock
        Cache::lock('botman.dennis')->get(function () use ($bot) {
            $this->sendDennisBier($bot);
        });
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
