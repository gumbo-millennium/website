<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A quote sent by a user via the Telegram bot.
 *
 * @property int $id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property null|\Illuminate\Support\Date $submitted_at
 * @property null|int $user_id
 * @property string $display_name
 * @property string $quote
 * @property-read null|User $user
 */
class BotQuote extends Model
{
    private const KEEP_DAYS = 45;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'submitted_at',
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
    public function scopeNotSubmitted(Builder $query): Builder
    {
        return $query->whereNull('submitted_at');
    }

    /**
     * Scope by unsubmitted quotes.
     */
    public function scopeOutdated(Builder $query): Builder
    {
        return $query->where(static function ($query) {
            $query->whereNotNull('submitted_at')
                ->andWhere('submitted_at', '<', now()->subDays(self::KEEP_DAYS));
        });
    }
}
