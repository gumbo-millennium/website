<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Jobs\Payments\UpdatePaymentJob;
use App\Models\GoogleWallet\EventObject;
use App\Models\Payment;
use App\Services\Payments\MolliePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use JsonException;

class WebhookController extends Controller
{
    private const GOOGLE_WALLET_PROTOCOL = 'ECv2SigningOnly';

    /**
     * Mollie payment events webhook.
     */
    public function mollie(Request $request): HttpResponse
    {
        $paymentId = (string) $request->input('id');

        $paymentModel = Payment::whereTransactionId(
            MolliePaymentService::getName(),
            $paymentId ?: Str::random(64),
        )->first();

        if ($paymentModel) {
            UpdatePaymentJob::dispatch($paymentModel);
        }

        return Response::noContent(HttpResponse::HTTP_OK);
    }

    /**
     * Responses from Google Wallet Passes API when a pass is added/removed from a wallet.
     */
    public function googleWallet(Request $request): HttpResponse
    {
        $payload = $request->json()->all();

        // Verify the request basics
        abort_unless(Arr::has($payload, ['protocolVersion', 'signature', 'intermediateSigningKey', 'signedMessage']), HttpResponse::HTTP_BAD_REQUEST);

        // Valid up to this point, store the result
        $now = Date::now()->format('Y-m-d-H-i-s');
        Storage::put("google-wallet/webhook/requests/{$now}.json", $request->getContent());

        // Verify the details
        abort_unless($payload['protocolVersion'] === self::GOOGLE_WALLET_PROTOCOL, HttpResponse::HTTP_BAD_REQUEST);

        // Decode the body
        try {
            $body = Collection::make(json_decode($payload['signedMessage'], true, 512, JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            abort(HttpResponse::HTTP_BAD_REQUEST);
        }

        // Check if all data is correct
        abort_unless($body->has(['classId', 'objectId', 'expTimeMillis', 'eventType', 'nonce']), HttpResponse::HTTP_BAD_REQUEST);
        abort_unless(in_array($body['eventType'], ['del', 'save'], true), HttpResponse::HTTP_BAD_REQUEST);
        abort_unless($body['expTimeMillis'] > (time() * 1000), HttpResponse::HTTP_BAD_REQUEST);

        // From this point on, for security purposes, report all requests as valid, even if they're not.

        // Verify the classId and objectId are valid in our scope
        $classId = $body['classId'];
        $objectId = $body['objectId'];
        $issuerId = Config::get('google-wallet.issuer_id');
        if (! Str::startsWith($classId, "{$issuerId}.") || ! Str::startsWith($objectId, "{$issuerId}.")) {
            return Response::noContent();
        }

        // Check for replay
        $nonce = $body['nonce'];
        if (Storage::exists("google-wallet/webhook/nonces/{$nonce}.temp")) {
            return Response::noContent();
        }

        // Store the nonce
        Storage::put("google-wallet/webhook/nonces/{$nonce}.temp", '');

        // Update the event object
        $action = $body['eventType'];
        EventObject::query()
            ->whereHas('class', fn ($query) => $query->where('wallet_id', $classId))
            ->where('wallet_id', $objectId)
            ->increment($action === 'del' ? 'removals' : 'redemptions');

        return Response::noContent();
    }
}
