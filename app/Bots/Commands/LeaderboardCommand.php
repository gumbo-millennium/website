<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Models\MemberReferral;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Actions;

class LeaderboardCommand extends Command
{
    private const EMOJI = ['🥇', '🥈', '🥉'];

    private const MESSAGE_EMPTY = <<<'HTML'
    🏆 Nieuwe Leden Leaderboard

    Het leaderboard is momenteel leeg 😔
    HTML;

    private const MESSAGE_TEMPLATE = <<<'HTML'
    🏆 Nieuwe Leden Leaderboard

    %s

    Zie je jezelf niet staan, maar hoor je er wel tussen?
    Controleer dan of je toestemming hebt gegeven om op dit bord te staan.
    HTML;

    /**
     * The name of the Telegram command.
     *
     * @var string
     */
    protected $name = 'leaderboard';

    /**
     * The Telegram command description.
     *
     * @var string
     */
    protected $description = 'Toon de (besloten) activiteiten';

    /**
     * Handle the activity
     */
    public function handle()
    {
        // Send typing status
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Members only
        $user = $this->getUser();
        if (!$this->ensureIsMember($user)) {
            return;
        }

        // Compute top 10
        $referrals = MemberReferral::query()
            ->select(
                'user_id',
                DB::raw('COUNT(*) as referral_count')
            )
            ->groupBy('user_id')
            ->orderByDesc('referral_count')
            ->with('user')
            ->has('user')
            ->take(5)
            ->get();

        if ($referrals->isEmpty()) {
            $this->replyWithMessage([
                'text' => self::MESSAGE_EMPTY,
            ]);
            return;
        }

        $ranks = [];
        foreach ($referrals as $offset => $referral) {
            assert($referral instanceof MemberReferral);

            $emojiOrListItem = self::EMOJI[$offset] ?? '-';
            $body = trans_choice(':user with :count member|:user with :count members', $referral->referral_count, [
                'user' => e($referral->user->leaderboard_name),
            ]);

            $ranks[] = "{$emojiOrListItem} {$body}";
        }

        // Return message
        $this->replyWithMessage([
            'text' => sprintf(self::MESSAGE_TEMPLATE, implode(PHP_EOL, $ranks)),
        ]);
    }
}
