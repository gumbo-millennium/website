<?php

declare(strict_types=1);

namespace App\Http\Controllers\EnrollNew;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireActiveEnrollment;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            'auth',
            RequireActiveEnrollment::class,
        ]);
    }

    public function show(Request $request, Activity $activity)
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
    }

    public function start(Request $request, Activity $activity): RedirectResponse
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
    }

    public function back(Request $request, Activity $activity)
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
    }

    public function verify(Request $request, Activity $activity): RedirectResponse
    {
        // TODO
        throw new HttpException(501, 'Not implemented');
    }
}
