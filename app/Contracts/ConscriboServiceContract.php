<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\ServiceException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

interface ConscriboServiceContract
{
    /**
     * Returns if the API is configured for use.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Attempts login with the API
     * @return void
     * @throws ServiceException
     */
    public function authorise(): void;

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
