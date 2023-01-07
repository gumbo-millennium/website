<?php

declare(strict_types=1);

namespace App\Listeners\Console;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;

/**
 * Listen on the optimize commands and run additonal commands.
 */
class CommandExtensionsListener
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(CommandStarting $event)
    {
        $call = fn (string $method, array $args = []) => Artisan::call($method, $args, $event->output);

        if ($event->command == 'optimize') {
            // Create versioned pages
            $call('gumbo:update-content');
        }

        if ($event->command == 'optimize:clear') {
            //
        }
    }
}
