<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Sends 404 or 410 on old routes.
 */
class LegacyController extends Controller
{
    /**
     * Sends a 404 error.
     *
     * @throws HttpException
     */
    public function notFound(): void
    {
        throw new NotFoundHttpException();
    }

    /**
     * Sends a 410 error.
     *
     * @throws HttpException
     */
    public function gone(): void
    {
        throw new GoneHttpException();
    }
}
