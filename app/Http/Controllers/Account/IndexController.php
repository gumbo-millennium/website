<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUserLink;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
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
        $telegramName = null;
        if ($user->telegram_id) {
            $telegramName = BotUserLink::getName('telegram', $user->telegram_id);
        }

        return Response::view('account.index', compact('user', 'telegramName'));
    }
}
