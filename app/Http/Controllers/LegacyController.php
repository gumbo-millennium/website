<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Sends 404 or 410 on old routes
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class LegacyController extends Controller
{
    /**
     * Sends a 404 error
     *
     * @return void
     * @throws HttpException
     */
    public function notFound(): void
    {
        throw new NotFoundHttpException();
    }

    /**
     * Sends a 410 error
     *
     * @return void
     * @throws HttpException
     */
    public function gone(): void
    {
        throw new GoneHttpException();
    }
}
