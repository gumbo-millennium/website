<?php

declare(strict_types=1);

namespace App\Listeners\Console;

use Illuminate\Console\Command;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Listen on the optimize commands and run additonal commands.
 */
class CommandExtensionsListener
{
    private OutputInterface $output;

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(CommandStarting $event)
    {
        $this->output = $event->output;

        if ($event->command == 'optimize') {
            // Create versioned pages
            $this->call('gumbo:update-content');
        }

        if ($event->command == 'optimize:clear') {
            //
        }
    }

    private function call(string $commandName, array $args = []): bool
    {
        return Artisan::call($commandName, $args, $this->output) === Command::SUCCESS;
    }
}
