<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Exceptions;

use App\Services\Conscribo\Enums\ConscriboErrorCodes;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

class ConscriboException extends RuntimeException
{
    /**
     * Create a new instance from a Conscribo Response.
     */
    public static function fromHttpResponse(Response $response, ?string $command = null): self
    {
        if (! $response->successful()) {
            return new static("Conscribo API error for {$command}: {$response->status()}: {$response->body()}", ConscriboErrorCodes::HttpError);
        }

        $results = $response->json('results.result');
        $failedResponses = Collection::make($results)->where('success', 0);
        $failedRequestSequence = $failedResponses->pluck('requestSequence')->first();
        $firstNotification = $failedResponses
            ->pluck('notifications.notification')
            ->collapse()
            ->first();

        $resultCode = ConscriboErrorCodes::fromNotification($firstNotification ?? '');
        $commandName = $command ?? $failedRequestSequence ?? 'unknown';

        return new static("Conscribo API error for {$commandName}: {$resultCode->name} ({$resultCode->value}): {$firstNotification}", $resultCode);
    }

    public function __construct(string $message, protected ConscriboErrorCodes $conscriboCode, ?Throwable $previous = null)
    {
        parent::__construct($message, $conscriboCode->value, $previous);
    }

    public function getConscriboCode(): ConscriboErrorCodes
    {
        return $this->conscriboCode;
    }
}
