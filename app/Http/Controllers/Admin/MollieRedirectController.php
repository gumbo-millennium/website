<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Shop\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Laravel\Facades\Mollie;

class MollieRedirectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function enrollment(Enrollment $enrollment, Request $request): RedirectResponse
    {
        Gate::authorize('view', $enrollment);

        // Fail if not a Mollie payment
        abort_unless($enrollment->mollie_id, 404);

        try {
            // Find order
            $mollieOrder = Mollie::api()->payments()->get($enrollment->mollie_id);

            // Find dashboard link
            $dashboardLink = object_get($mollieOrder, '_links.dashboard.href');

            // Fail if no dashboard link
            abort_unless($dashboardLink, 404);

            // Redirect to dashboard
            return Response::redirectTo($dashboardLink)->header('Cache-Control', 'no-store');
        } catch (ApiException $error) {
            // Throw a 404 if the payment is not found upstream
            if ($error->getCode() === 404) {
                abort(404);
            }

            // Okay, no idea here
            report($error);
            abort(500);
        }
    }

    public function order(Order $order, Request $request): RedirectResponse
    {
        Gate::authorize('view', $order);

        // Fail if not a Mollie payment
        abort_unless($order->payment_id, 404);

        try {
            // Find order
            $mollieOrder = Mollie::api()->orders()->get($order->payment_id);

            // Find dashboard link
            $dashboardLink = object_get($mollieOrder, '_links.dashboard.href');

            // Fail if no dashboard link
            abort_unless($dashboardLink, 404);

            // Redirect to dashboard
            return Response::redirectTo($dashboardLink)->header('Cache-Control', 'no-store');
        } catch (ApiException $error) {
            // Throw a 404 if the payment is not found upstream
            if ($error->getCode() === 404) {
                abort(404);
            }

            // Okay, no idea here
            report($error);
            abort(500);
        }
    }
}
