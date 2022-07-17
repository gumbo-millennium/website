<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Laravel\Sanctum\PersonalAccessToken;

class PersonalAccessTokenController extends Controller
{
    /**
     * Displays existing access tokens.
     */
    public function index(Request $request): HttpResponse
    {
        $user = $request->user();

        return Response::view('account.tokens', [
            'tokens' => $user->tokens,
            'newToken' => $request->session()->get('created_token'),
        ]);
    }

    /**
     * Issues new access tokens.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:200',
            ],
        ]);

        $token = $request->user()->createToken($request->name);

        flash()->success('API token created successfully.');

        return Response::redirectToRoute('account.tokens.index')
            ->with('created_token', $token);
    }

    /**
     * Revokes access tokens.
     */
    public function delete(Request $request, PersonalAccessToken $token): RedirectResponse
    {
        $user = $request->user();

        abort_unless($token->tokenable->is($user), HttpResponse::HTTP_NOT_FOUND);

        $user->tokens()->where('id', $token->id)->delete();

        flash()->success('API token revoked successfully.');

        return Response::redirectToRoute('account.tokens.index');
    }
}
