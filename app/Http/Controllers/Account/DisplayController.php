<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Helpers\Str;
use App\Http\Controllers\Controller;
use App\Models\BotUserLink;
use App\Models\Webcam;
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
     * Force auth.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Index page.
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
     */
    public function showUrls(Request $request): HttpResponse
    {
        // Shorthands
        $user = $request->user();
        $urls = new Collection();

        // Webcam view
        if ($request->user()->hasPermissionTo('plazacam-view')) {
            $urlExpire = Date::now()->addMonths(6);
            foreach (Webcam::all() as $webcam) {
                $urls->push([
                    'id' => Str::slug("view-cam-{$webcam->id}"),
                    'group' => 'Webcams',
                    'expires' => $urlExpire,
                    'title' => $webcam->name,
                    'url' => URL::signedRoute('api.webcam.view', [
                        'user' => $user,
                        'webcam' => $webcam,
                    ], $urlExpire),
                ]);
            }
        }

        // Webcam update
        if ($request->user()->hasPermissionTo('plazacam-update')) {
            $urlExpire = Date::now()->addMonths(3);
            foreach (Webcam::all() as $webcam) {
                $urls->push([
                    'id' => Str::slug("update-cam-{$webcam->id}"),
                    'group' => 'Webcams (bijwerken)',
                    'expires' => $urlExpire,
                    'title' => "{$webcam->name} (update)",
                    'url' => URL::signedRoute('api.webcam.store', [
                        'user' => $user,
                        'webcam' => $webcam,
                    ], $urlExpire),
                ]);
            }
        }

        // Render view
        return Response::view('account.urls', [
            'urls' => $urls->groupBy('group'),
        ])
            ->setPrivate()
            ->setMaxAge(60);
    }
}
