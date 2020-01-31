<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The enrollment could not be found
 */
class EnrollmentNotFoundException extends NotFoundHttpException implements Responsable
{
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
