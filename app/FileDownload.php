<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Builder;

/**
 * An individual file download, logs the file downloaded,
 * the user, the timestamp and the IP from which the user downloaded.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileDownload extends Pivot
{
    /**
     * Categories don't have timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Disable incrementing primary key
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Set key type to string
     *
     * @var string
     */
    public $keyType = 'string';

    /**
     * The IP is fillable, others aren't
     *
     * @var array
     */
    public $fillable = [
        'ip'
    ];

    /**
     * A file download has a download date
     *
     * @var array
     */
    public $dates = [
        'downloaded_at'
    ];

    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        // Forward first
        parent::boot();

        // Generate UUID on create and add downloaded_at
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
            $model->downloaded_at = now();
        });
    }

    /**
     * Removes the IP address of FileDownload objects older than 90 days.
     *
     * @return int number of removed IPs
     */
    public static function removeIpOnOldEntries() : int
    {
        return self::query()
            ->where('downloaded_at', '<', today()->subDays(90))
            ->whereNotNull('ip')
            ->update(['ip' => null])
            ->count();
    }
}
