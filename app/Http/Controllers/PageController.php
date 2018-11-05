<?php

namespace App\Http\Controllers;

use Corcel\Model\Page;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles most page calls
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PageController extends Controller
{
    /**
     * Handles calls to /.well-known/dnt and /.well-known/dnt/{session-id}
     *
     * @param PrivacyProvider $provider
     * @param string $sessionId
     * @return JsonResponse
     */
    public function homepage()
    {
        $homepage = Page::home()->first();
        if ($homepage === null) {
            throw new NotFoundHttpException;
        }

        return view('page')->with(['page' => $homepage]);
    }
}
