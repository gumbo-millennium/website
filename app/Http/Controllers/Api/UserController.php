<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function whoami(Request $request)
    {
        $user = $request->user();

        abort_unless($user, HttpResponse::HTTP_BAD_REQUEST);

        return UserResource::make($user);
    }
}
