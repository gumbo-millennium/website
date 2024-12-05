<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\BotQuoteReaction.
 *
 * @property int $id
 * @property int $quote_id
 * @property int $user_id
 * @property null|string $reaction
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\BotQuote $quote
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\BotQuoteReactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|BotQuoteReaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BotQuoteReaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BotQuoteReaction query()
 * @mixin \Eloquent
 */
class BotQuoteReaction extends Model
{
    use HasFactory;
    use Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote_id',
        'user_id',
        'reaction',
    ];

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return $this->query()
            ->whereNull('reaction');
    }

    /**
     * Get the quote that owns the reaction.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(BotQuote::class);
    }

    /**
     * Get the user that owns the reaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
