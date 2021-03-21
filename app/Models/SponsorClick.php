<?php

declare(strict_types=1);

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * A collection of clicks on a sponsor for a given day
 *
 * @property int $id
 * @property int $sponsor_id
 * @property int $count
 * @property \Illuminate\Support\Date $date
 * @property-read Sponsor $sponsor
 */
class SponsorClick extends Model
{
    private const INCREMENT_QUERY = <<<'SQL'
        INSERT INTO %s (`sponsor_id`, `date`)
        VALUES (?, NOW())
        ON DUPLICATE KEY UPDATE `count` = `count` + 1;
    SQL;

    /**
     * Increments the number of clicks for this sponsor for today
     *
     * @param Sponsor $sponsor
     * @return void
     * @throws QueryException
     */
    public static function addClick(Sponsor $sponsor): void
    {
        // Get sanity in here
        if (!$sponsor->exists()) {
            throw new InvalidArgumentException('Invalid sponsor supplied to increment.');
        }

        // Run a prepared statement
        DB::statement(
            sprintf(self::INCREMENT_QUERY, (new SponsorClick())->getTable()),
            [$sponsor->id]
        );
    }

    /**
     * Ensure a date is always set
     *
     * @param Closure|string $callback
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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'count' => 'int',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date',
    ];

    /**
     * Returns owning sponsor
     *
     * @return BelongsTo
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }
}
