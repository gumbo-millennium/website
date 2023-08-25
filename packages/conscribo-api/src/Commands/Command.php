<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Commands;

use Gumbo\ConscriboApi\Contracts\ConscriboApiClient;
use Illuminate\Console\Command as IlluminateCommand;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends IlluminateCommand
{
    protected ConscriboApiClient $client;

    /**
     * Write a string as standard output when verbository is 'verbose' or higher.
     */
    protected function verboseLine(string $string, ?string $style): void
    {
        $this->line($string, $style, OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * Write a string as standard output when verbository is 'very verbose' or higher.
     */
    protected function veryVerboseLine(string $string, ?string $style): void
    {
        $this->line($string, $style, OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    /**
     * Write a string as standard output when verbository is 'debug' or higher.
     */
    protected function debugLine(string $string, ?string $style): void
    {
        $this->line($string, $style, OutputInterface::VERBOSITY_DEBUG);
    }
}
