<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Facades\Enroll;
use App\Models\Activity;
use App\Models\Enrollment;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class RequireActiveEnrollment
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $activity = $request->route('activity');

        if (is_string($activity)) {
            $activity = Activity::query()
                ->where((new Activity())->getRouteKeyName(), $request->route('activity'))
                ->firstOrFail();
        }

        if (! $enrollment = Enroll::getEnrollment($activity)) {
            flash()->warning(__(
                "You're currently not enrolled into :activity",
                ['activity' => $activity->name],
            ));

            return Response::redirectToRoute('enroll.create', [$activity]);
        }

        App::instance(Enrollment::class, $enrollment);

        return $next($request);
    }
}
