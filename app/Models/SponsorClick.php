<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * App\Models\SponsorClick.
 *
 * @property int $id
 * @property int $sponsor_id
 * @property int $count
 * @property \Illuminate\Support\Carbon $date
 * @property-read \App\Models\Sponsor $sponsor
 * @method static \Illuminate\Database\Eloquent\Builder|SponsorClick newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SponsorClick newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SponsorClick query()
 * @mixin \Eloquent
 */
class SponsorClick extends Model
{
    private const INCREMENT_QUERY = <<<'SQL'
        INSERT INTO %s (`sponsor_id`, `date`)
        VALUES (?, NOW())
        ON DUPLICATE KEY UPDATE `count` = `count` + 1;
    SQL;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'count' => 'int',
        'date' => 'datetime',
    ];

    /**
     * Increments the number of clicks for this sponsor for today.
     *
     * @throws QueryException
     */
    public static function addClick(Sponsor $sponsor): void
    {
        // Get sanity in here
        if (! $sponsor->exists()) {
            throw new InvalidArgumentException('Invalid sponsor supplied to increment.');
        }

        // Run a prepared statement
        DB::statement(
            sprintf(self::INCREMENT_QUERY, (new self())->getTable()),
            [$sponsor->id],
        );
    }

    /**
     * Ensure a date is always set.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(static function ($click) {
            if ($click->date !== null) {
                return;
            }

            $click->date = now();
        });
    }

    /**
     * Returns owning sponsor.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }
}
