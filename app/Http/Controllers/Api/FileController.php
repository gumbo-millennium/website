<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

/**
 * Handles uploaded files via the API
 */
class FileController extends Controller
{
    public function receive(Request $request)
    {
        abort(501, 'TODO');
    }
}
