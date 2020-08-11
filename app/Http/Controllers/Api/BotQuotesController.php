<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotQuote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BotQuotesController extends Controller
{
    /**
     * Ensure sane defaults
     * @return void
     */
    public function __construct()
    {
        $this->middleware('signed');
        $this->middleware('throttle:30,10')->only('submit');
    }

    /**
     * Submits a new quote, should be rate-limited
     * @param Request $request
     * @return JsonResponse
     */
    public function submit(Request $request)
    {
        // Validate request
        $valid = $request->validate([
            'name' => 'required|string|min:2|max:200',
            'message' => 'required|string|min:2'
        ]);

        // Store model
        $quote = BotQuote::create([
            'user_id' => \optional($request->user())->id,
            'display_name' => $valid['name'],
            'quote' => $valid['quote']
        ]);

        // Return
        return \response()->json($quote, Response::HTTP_CREATED);
    }

    /**
     * Returns some recent quotes for a given user
     * @param User $user
     * @return JsonResponse
     */
    public function display(User $user)
    {
        // Get quotes
        $quotes = BotQuote::query()
            ->notOutdated()
            ->whereUserId($user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Return data
        return \response()->json($quotes);
    }
}
