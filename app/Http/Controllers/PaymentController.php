<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Facades\Payments;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Shop\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Spatie\Csp\Directive;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Render a waiting page for the payment.
     * @return HttpResponse|RedirectResponse
     */
    public function show(Request $request, Payment $payment)
    {
        abort_unless($request->user()->is($payment->user), HttpResponse::HTTP_NOT_FOUND);

        if ($payment->is_stable) {
            return Response::redirectToRoute('payment.verify', [$payment]);
        }

        if ($request->has('verify')) {
            return Response::view('payment.wait-verify')
                ->header('Refresh', sprintf('0; url=%s', route('payment.verify', [$payment])));
        }

        return Response::view('payment.wait-redirect')
            ->header('Refresh', sprintf('0; url=%s', route('payment.redirect', [$payment])));
    }

    public function redirect(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($request->user()->is($payment->user), HttpResponse::HTTP_NOT_FOUND);

        if ($payment->is_stable) {
            return Response::redirectToRoute('payment.verify', [$payment]);
        }

        try {
            $service = Payments::find($payment->provider);
        } catch (PaymentException $e) {
            flash()
                ->error(__("The payment provider isn't available right now."));

            return $this->getDestination($payment);
        }

        try {
            $next = $service->nextUrl($payment);

            if ($next) {
                // Add redirect header
                $redirectProto = parse_url($next, PHP_URL_SCHEME);
                $redirectDomain = parse_url($next, PHP_URL_HOST);

                // Add CSP records
                $this->addToCsp(["{$redirectProto}://{$redirectDomain}/"], Directive::CONNECT);

                // Perform redirect
                return Response::redirectTo($next);
            }

            flash()->warning(
                __('This payment is currently processing, please hold.'),
            );

            return $this->getDestination($payment);
        } catch (PaymentException $e) {
            report($e);

            flash()
                ->warning(__('Could not start payment: :message.', ['message' => rtrim($e->getMessage(), '.')]));

            return $this->getDestination($payment);
        }
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($request->user()->is($payment->user), HttpResponse::HTTP_NOT_FOUND);

        // Determine wait time in microseconds
        $sleepDuration = Config::get('gumbo.payments.verify.refresh_rate', 500) * 1000;
        $maxDuration = Config::get('gumbo.payments.verify.timeout', 5_000) * 1000;

        // Ensure some values are sane, making us wait for a maximum of 10 seconds
        $sleepDuration = min($sleepDuration, 1_000_000);
        $maxDuration = min($maxDuration, 10_000_000);

        // Determine safe maximum iteration count
        $maxIterations = ceil($maxDuration / min($sleepDuration, 1));

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $payment->refresh();

            // Check if stable
            if ($payment->is_stable) {
                flash()->success(
                    __('The payment has been validated successfully.'),
                );

                return $this->getDestination($payment);
            }

            // Sleep
            usleep($sleepDuration);
        }

        flash()->info(
            __('The payment is still processing or has failed, please try again.'),
        );

        return $this->getDestination($payment);
    }

    private function getDestination(Payment $payment): RedirectResponse
    {
        $payable = $payment->payable;
        if ($payable instanceof Enrollment) {
            return Response::redirectToRoute('enroll.show', [$payable->activity]);
        }

        if ($payable instanceof Order) {
            return Response::redirectToRoute('shop.order.show', [$payable]);
        }

        return Response::redirectToRoute('home');
    }
}
