<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Console\OutputStyle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Adds or updates the default user.
 */
abstract class VerboseSeeder extends Seeder
{
    /**
     * Prints a line.
     *
     * @param array|string $message Message to print, array to pass to sprintf
     */
    public function writeln($message, int $options = OutputStyle::OUTPUT_NORMAL): void
    {
        $message = Arr::wrap($message);
        if (count($message) > 1) {
            $message = sprintf(...$message);
        }
        $this->command->getOutput()->writeln($message, $options);
    }

    /**
     * Prints a debug line.
     */
    public function error(...$args): void
    {
        $message = array_shift($args);

        $this->writeln(
            sprintf("<error>{$message}</>", ...$args),
            OutputStyle::OUTPUT_NORMAL | OutputStyle::VERBOSITY_QUIET,
        );
    }

    /**
     * Prints a debug line.
     */
    public function log(...$args): void
    {
        $this->writeln($args, OutputStyle::OUTPUT_NORMAL | OutputStyle::VERBOSITY_VERBOSE);
    }

    /**
     * Prints a debug line.
     */
    public function debug(...$args): void
    {
        $this->writeln($args, OutputStyle::OUTPUT_NORMAL | OutputStyle::VERBOSITY_DEBUG);
    }
}
