<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
