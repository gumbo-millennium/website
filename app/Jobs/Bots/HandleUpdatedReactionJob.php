<?php

declare(strict_types=1);

namespace App\Jobs\Bots;

use App\Models\BotQuote;
use App\Models\BotQuoteReaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

/**
 * @method static PendingDispatch dispatch(Update $update)
 * @method static PendingDispatch dispatchIf(bool $boolean, Update $update)
 * @method static PendingDispatch dispatchUnless(bool $boolean, Update $update)
 * @method static PendingDispatch dispatchSync(Update $update)
 * @method static PendingDispatch dispatchAfterResponse(Update $update)
 */
class HandleUpdatedReactionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly Update $update,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check for a quote with this ID
        if (! isset($this->update->message_reaction)) {
            return;
        }

        $updatedReaction = $this->update->message_reaction;

        $messageId = object_get($updatedReaction, 'message_id');
        $telegramUser = object_get($updatedReaction, 'user');

        if (! $messageId || ! $telegramUser) {
            Log::info('Tried to derrive messageId and telegramUser from {message_reaction}, but it was unsuccesful (obviously).', [
                'message_reaction' => $updatedReaction,
            ]);

            return;
        }

        // Check if the user is a bot
        if (object_get($telegramUser, 'is_bot')) {
            Log::info("Got reply by a bot, that's peculiar. Not handling it.", [
                'message_reaction' => $updatedReaction,
            ]);

            return;
        }

        // Find the user and quote for the IDs
        $user = User::whereTelegramId($telegramUser->id)->first();
        $quote = BotQuote::where(
            fn (Builder $query) => $query
                ->orWhere([
                    'message_id' => $messageId,
                    'reply_id' => $messageId,
                ]),
        )->first();

        if (! $user || ! $quote) {
            Log::info('Tried to find message and user for {message_id} and {user_id}, but one or both were not found.', [
                'message_id' => $messageId,
                'user_id' => $telegramUser->id,
            ]);

            return;
        }

        // Map reaction array to strings
        $reactionAsString = Collection::make($updatedReaction->new_reaction ?? [])
            ->map(fn ($reaction) => $reaction->emoji ?? 'custom')
            ->join(', ');

        // Create or update a reaction
        $reaction = BotQuoteReaction::updateOrCreate([
            'user_id' => $user->id,
            'quote_id' => $quote->id,
        ], [
            'reaction' => $reactionAsString ?: null,
        ]);

        Log::info('Updated reaction for quote {quote_id} by user {user_id} to {reaction}.', [
            'reaction_id' => $reaction->id,
            'quote_id' => $quote->id,
            'user_id' => $user->id,
            'reaction' => $updatedReaction->reaction,
            'reaction_object' => $updatedReaction->new_reaction,
            'reaction_string' => $reactionAsString,
        ]);
    }
}
