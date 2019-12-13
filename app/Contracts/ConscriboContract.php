<?php

declare(strict_types=1);

namespace App\Contracts;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

interface ConscriboContract
{
    /**
     * Returns if the API is configured for use.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Runs the given command on the API
     *
     * @param string $command
     * @param array $args
     * @return array|null
     * @throws HttpExceptionInterface on API failure
     */
    public function runCommand(string $command, array $args): array;
}
