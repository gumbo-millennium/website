<?php

declare(strict_types=1);

use Illuminate\Console\OutputStyle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Adds or updates the default user
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class VerboseSeeder extends Seeder
{
    /**
     * Prints a line
     * @param string|array $message Message to print, array to pass to sprintf
     * @param int $options
     * @return void
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
     * Prints a debug line
     * @param mixed ...$args
     * @return void
     */
    public function error(...$args): void
    {
        $this->writeln("<error>{$args}</error>", OutputStyle::OUTPUT_NORMAL | OutputStyle::VERBOSITY_QUIET);
    }

    /**
     * Prints a debug line
     * @param mixed ...$args
     * @return void
     */
    public function log(...$args): void
    {
        $this->writeln($args, OutputStyle::OUTPUT_NORMAL | OutputStyle::VERBOSITY_VERBOSE);
    }

    /**
     * Prints a debug line
     * @param mixed ...$args
     * @return void
     */
    public function debug(...$args): void
    {
        $this->writeln($args, OutputStyle::OUTPUT_NORMAL | OutputStyle::VERBOSITY_DEBUG);
    }
}
