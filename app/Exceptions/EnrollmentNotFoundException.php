<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Activity;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The enrollment could not be found.
 */
class EnrollmentNotFoundException extends NotFoundHttpException implements Responsable
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $activity = $request['activty'] ?? null;
        if ($activity instanceof Activity || \is_string($activity)) {
            return \response()
                ->redirectToRoute('activity.show', ['activity' => $request['activity']]);
        }

        return \response()
            ->redirectToRoute('activity.index');
    }
}
