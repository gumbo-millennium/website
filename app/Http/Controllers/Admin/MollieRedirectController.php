<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Shop\Order;
use App\Services\Payments\MolliePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrder;
use Mollie\Laravel\Facades\Mollie;

class MollieRedirectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Payment $payment, Request $request): RedirectResponse
    {
        Gate::authorize('view', $payment);

        // Fail if not a Mollie payment
        abort_unless($payment->provider === MolliePaymentService::getName(), 404);

        try {
            // Find order
            /** @var MollieOrder $mollieOrder */
            $mollieOrder = Mollie::api()->orders()->get($payment->transaction_id);

            // Find dashboard link
            $dashboardLink = (string) object_get($mollieOrder, '_links.dashboard.href');

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
