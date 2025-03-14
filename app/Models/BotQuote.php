<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\HtmlString;

/**
 * App\Models\BotQuote.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $submitted_at
 * @property null|int $user_id
 * @property string $display_name
 * @property string $quote
 * @property null|int $message_id
 * @property null|string $reply_id
 * @property-read HtmlString $formatted_quote
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BotQuoteReaction> $reactions
 * @property-read null|\App\Models\User $user
 * @method static \Database\Factories\BotQuoteFactory factory($count = null, $state = [])
 * @method static Builder|BotQuote newModelQuery()
 * @method static Builder|BotQuote newQuery()
 * @method static Builder|BotQuote notSubmitted()
 * @method static Builder|BotQuote outdated()
 * @method static Builder|BotQuote query()
 * @method static Builder|BotQuote withReactionCount()
 * @method static Builder|BotQuote withUser()
 * @mixin \Eloquent
 */
class BotQuote extends Model
{
    use HasFactory;
    use Prunable;

    private const KEEP_DAYS = 45;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'display_name',
        'quote',
        'message_id',
        'reply_id',
    ];

    /**
     * Returns owning user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope by unsubmitted quotes.
     */
    public function scopeNotSubmitted(Builder $query): void
    {
        $query->whereNull('submitted_at');
    }

    public function scopeWithUser(Builder $query): void
    {
        $query->with('user:id,first_name,insert,last_name,alias');
    }

    /**
     * Scope by unsubmitted quotes.
     */
    public function scopeOutdated(Builder $query): void
    {
        $query->where(
            fn ($query) => $query
                ->whereNotNull('submitted_at')
                ->andWhere('submitted_at', '<', now()->subDays(self::KEEP_DAYS)),
        );
    }

    public function getFormattedQuoteAttribute(): HtmlString
    {
        return new HtmlString(nl2br(e($this->quote)));
    }

    /**
     * Get the reactions for the quote.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(BotQuoteReaction::class, 'quote_id');
    }

    /**
     * Adds a reaction_count which is basically just a number of people that
     * interacted with the post.
     */
    public function scopeWithReactionCount(Builder $query): void
    {
        $query->withCount([
            'reactions as reaction_count' => fn (Builder $query) => $query->whereNotNull('reaction'),
        ]);
    }

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return $this->query()
            ->whereNotNull('created_at')
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<', Date::today()->subMonths(18));
    }
}
