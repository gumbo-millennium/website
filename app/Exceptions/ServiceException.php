<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * The enrollment could not be found
 */
class ServiceException extends RuntimeException
{
    public const SERVICE_CONSCRIBO = 'conscribo';
    public const SERVICE_STRIPE = 'stripe';
    public const SERVICE_GOOGLE = 'google';

    /**
     * Service that did boom
     * @var string
     */
    protected string $service;

    /**
     * Creates a new service error
     *
     * @param string $service
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return void
     */
    public function __construct(string $service, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->service = $service;
    }

    /**
     * Returns guilty service
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return \response()
            ->redirectToRoute('activity.show', ['activity' => $request->get('activity')]);
    }
}
