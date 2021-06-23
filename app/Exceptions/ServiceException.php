<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * The enrollment could not be found.
 */
class ServiceException extends RuntimeException
{
    public const SERVICE_CONSCRIBO = 'conscribo';

    public const SERVICE_STRIPE = 'stripe';

    public const SERVICE_GOOGLE = 'google';

    /**
     * Service that did boom.
     */
    protected string $service;

    /**
     * Creates a new service error.
     *
     * @return void
     */
    public function __construct(
        string $service,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->service = $service;
    }

    /**
     * Returns guilty service.
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return \response()
            ->redirectToRoute('activity.show', ['activity' => $request->get('activity')]);
    }
}
