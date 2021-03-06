<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUserLink;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

/**
 * Allows a user to view details and API urls.
 */
class DisplayController extends Controller
{
    /**
     * Force auth
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Index page
     *
     * @param Request $request
     * @return HttpResponse
     */
    public function index(Request $request): HttpResponse
    {
        $user = $request->user();
        $telegramName = null;
        if ($user->telegram_id) {
            $telegramName = BotUserLink::getName('telegram', $user->telegram_id);
        }

        return response()
            ->view('account.index', compact('user', 'telegramName'))
            ->setPrivate();
    }

    /**
     * API urls for the user to (ab)use.
     *
     * @param Request $request
     * @return HttpResponse
     */
    public function viewUrls(Request $request): HttpResponse
    {
        // Shorthands
        $user = $request->user();
        $urlExpire = Date::now()->addYear()->diffInSeconds();

        $urls = new Collection();

        // Plazacam view
        if ($request->user()->hasPermissionTo('plazacam-view')) {
            // Plazacam
            $urls->push([
                'expires' => true,
                'title' => 'Plazacam',
                'url' => URL::signedRoute('api.plazacam.view', [
                    'user' => $user->id,
                    'image' => 'plaza',
                ], $urlExpire),
            ]);

            // Coffeecam
            $urls->push([
                'expires' => true,
                'title' => 'Koffiecam',
                'url' => URL::signedRoute('api.plazacam.view', [
                    'user' => $user->id,
                    'image' => 'coffee',
                ], $urlExpire),
            ]);
        }

        // Plazacam update
        if ($request->user()->hasPermissionTo('plazacam-update')) {
            // Plazacam
            $urls->push([
                'expires' => true,
                'title' => 'Plazacam (update)',
                'url' => URL::signedRoute('api.plazacam.store', [
                    'user' => $user->id,
                    'image' => 'plaza',
                ], $urlExpire),
            ]);

            // Coffeecam
            $urls->push([
                'expires' => true,
                'title' => 'Koffiecam (update)',
                'url' => URL::signedRoute('api.plazacam.store', [
                    'user' => $user->id,
                    'image' => 'coffee',
                ], $urlExpire),
            ]);
        }

        // Render view
        return Response::view('account.urls', compact('urls'))
            ->setPrivate()
            ->setMaxAge(60);
    }
}
