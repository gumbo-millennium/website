<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;

/**
 * Allows a user to view details and API urls.
 */
class IndexController extends Controller
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

        $recognizedRoles = $user->roles()->whereNotNull('conscribo_id')->pluck('title');

        $isMember = $user->is_member;

        return Response::view('account.index', [
            'user' => $user,
            'isMember' => $isMember,
            'recognizedRoles' => $recognizedRoles,
        ]);
    }

    /**
     * Allows users to request account updates.
     */
    public function requestUpdate(Request $request): RedirectResponse
    {
        $user = $request->user();
        $cacheKey = "update.triggered.{$user->id}";
        if (Cache::get($cacheKey) > Date::now()->subMinutes(5)) {
            flash()->warning(__('An account update has recently been requested. Please calm down.'));

            return Response::redirectToRoute('account.index');
        }

        Cache::put($cacheKey, Date::now(), Date::now()->addHour());

        Artisan::queue('gumbo:user:update', [
            'user' => $user->id,
        ]);

        flash()->success(__('An account update has been requested and should be processed within a few minutes.'));

        return Response::redirectToRoute('account.index');
    }
}
