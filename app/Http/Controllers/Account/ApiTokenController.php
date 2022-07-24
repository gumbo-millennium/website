<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;

class ApiTokenController extends Controller
{
    /**
     * Displays existing access tokens.
     */
    public function index(Request $request): HttpResponse
    {
        $user = $request->user();

        return Response::view('account.tokens.index', [
            'tokens' => $user->tokens,
            'newToken' => $request->session()->get('created_token'),
        ]);
    }

    /**
     * Creates a new API token.
     */
    public function create(): HttpResponse
    {
        return Response::view('account.tokens.create');
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
                'min:2',
                'max:200',
            ],
        ]);

        $token = $request->user()->createToken($request->name, ['openapi']);

        return Response::redirectToRoute('account.tokens.index')
            ->with('created_token', $token);
    }

    /**
     * Revokes access tokens.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->input('token'), HttpResponse::HTTP_BAD_REQUEST);

        $token = $request->user()->tokens()->find($request->input('token'));

        abort_unless($token, HttpResponse::HTTP_NOT_FOUND);

        $token->delete();

        flash()->success(__('API token revoked successfully.'));

        return Response::redirectToRoute('account.tokens.index');
    }
}
