<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BotQuoteResource;
use App\Http\Resources\Api\RealNameBotQuoteResource;
use App\Models\BotQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class BotQuoteController extends Controller
{
    public function __construct()
    {
        // Ensure JSON is supported
        $this->middleware('accept-json');

        // Limit expensive /book request to 1 per minute.
        $this->middleware('throttle:1,1')->only('book');
    }

    /**
     * Renders the user's sent quotes.
     */
    public function index(Request $request): iterable
    {
        $user = $request->user();

        $quotes = BotQuote::query()
            ->where('created_at', '>=', Date::now()->subMonth())
            ->orderBy('created_at')
            ->whereNotNull('submitted_at')
            ->where('user_id', $user->id)
            ->get();

        return BotQuoteResource::collection($quotes);
    }

    /**
     * Renders all sent quotes for the last month.
     */
    public function indexAll(): iterable
    {
        $this->authorize('quotes-export');

        $quotes = BotQuote::query()
            ->where('created_at', '>=', Date::now()->subMonth())
            ->orderBy('created_at')
            ->whereNotNull('submitted_at')
            ->get();

        return BotQuoteResource::collection($quotes);
    }

    /**
     * Returns all quotes from users that have been submitted over the last year, all the way back to December 5th.
     */
    public function book(): iterable
    {
        $this->authorize('quotes-export');

        // Find the /next/ december 5th
        $lastDecemberFifth = Date::make('December 5th')->endOfDay();
        if ($lastDecemberFifth->isAfter(Date::now())) {
            $lastDecemberFifth->subYear();
        }

        $quotes = BotQuote::query()
            ->where('created_at', '>', $lastDecemberFifth->startOfDay())
            ->whereNotNull('submitted_at')
            ->get();

        return RealNameBotQuoteResource::collection($quotes);
    }
}
