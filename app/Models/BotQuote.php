<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property-read null|\App\Models\User $user
 * @method static Builder|BotQuote newModelQuery()
 * @method static Builder|BotQuote newQuery()
 * @method static Builder|BotQuote notSubmitted()
 * @method static Builder|BotQuote outdated()
 * @method static Builder|BotQuote query()
 * @mixin \Eloquent
 */
class BotQuote extends Model
{
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
