<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PrivacyProvider;
use Illuminate\Http\JsonResponse;

/**
 * Handles calls about privacy
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PrivacyController extends Controller
{
    /**
     * Handles calls to /.well-known/dnt and /.well-known/dnt/{session-id}
     *
     * @param PrivacyProvider $provider
     * @param string $sessionId
     * @return JsonResponse
     */
    public function wellKnownResponse(PrivacyProvider $provider, string $sessionId = null) : JsonResponse
    {
        return response()->json([
            'tracking' => $provider->getTrackingStatusValue()
        ]);
    }
}
