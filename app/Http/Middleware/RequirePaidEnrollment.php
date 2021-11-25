<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Enrollment;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class RequirePaidEnrollment
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $enrollment = App::get(Enrollment::class);

        if ($enrollment !== null && $enrollment->price === null) {
            return Response::redirectToRoute('enroll.show', [$enrollment->activity]);
        }

        return $next($request);
    }
}
