<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MollieWebhookRequest;
use App\Models\Invoice;
use App\Services\Payments\MolliePaymentProvider;
use App\Services\PaymentService;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;

class MollieWebhookController extends Controller
{
    public function handle(PaymentService $service, MollieWebhookRequest $request)
    {
        // Get the invoice for this service
        $invoice = Invoice::whereProviderId('mollie', $request->id)->first();

        // Fail quietly
        if (!$invoice) {
            return Response::make(HttpResponse::HTTP_ACCEPTED);
        }

        // Make sure the ID is for Mollie
        $provider = $service->getProvider($invoice);
        if (!$provider instanceof MolliePaymentProvider) {
            return Response::make(HttpResponse::HTTP_ACCEPTED);
        }

        // Trigger an update
        $provider->updatePayment($invoice);
        return Response::make(HttpResponse::HTTP_ACCEPTED);
    }
}
