<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BotQuote;
use Illuminate\Http\Request;

/**
 * Lists the user's quote, and allows them to delete the ones not sent yet.
 */
class BotQuoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get user
        $user = $request->user();

        // Get user quotes
        $quotesQuery = BotQuote::whereUserId($user->id);
        $unsentQuotes = (clone $quotesQuery)->whereNull('submitted_at')->paginate(20);
        $sentQuotes = (clone $quotesQuery)->whereNotNull('submitted_at')->paginate(20, ['*'], 'sent-page');

        // Render view
        return response()
            ->view('account.quotes', [
                'unsent' => $unsentQuotes,
                'sent' => $sentQuotes
            ])
            ->setPrivate()
            ->setExpires(now()->subYear());
    }

    /**
     * Remove the specified resource from storage.
     * @param  \App\Models\BotQuote  $quote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // Get user
        $userId = $request->user()->id;
        $quoteId = $request->post('quote-id');

        // Get quote
        $quote = BotQuote::where([
            'id' => $quoteId,
            'user_id' => $userId
        ])->firstOrFail(['id', 'submitted_at']);

        // Skip if sent
        if ($quote->submitted_at !== null) {
            flash('Dit wist-je-datje is al verzonden.')->warning();
            return response()
                ->redirectToRoute('account.quotes')
                ->setPrivate();
        }

        // Delete message
        $quote->delete();

        // Redirect back
        flash('Wist-je-datje verwijderd.')->success();
        return response()
            ->redirectToRoute('account.quotes')
            ->setPrivate();
    }
}
