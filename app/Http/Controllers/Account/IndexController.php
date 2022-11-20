<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
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

        $recognizedRoles = $user->roles()->whereNotNull('conscribo_id')->pluck('title');

        $isMember = $user->is_member;

        return Response::view('account.index', [
            'user' => $user,
            'isMember' => $isMember,
            'recognizedRoles' => $recognizedRoles,
        ]);
    }
}
