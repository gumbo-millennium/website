<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Payments\UpdatePaymentJob;
use App\Models\Payment;
use App\Services\Payments\MolliePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;

class WebhookController extends Controller
{
    public function mollie(Request $request): HttpResponse
    {
        $paymentId = $request->input('id');

        $paymentModel = Payment::whereTransactionId(
            MolliePaymentService::getName(),
            $paymentId,
        )->first();

        if ($paymentModel) {
            UpdatePaymentJob::dispatch($paymentModel);
        }

        return Response::noContent(HttpResponse::HTTP_OK);
    }
}
