<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Ramsey\Uuid\Uuid;

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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_downloads';

    /**
     * The IP is fillable, others aren't
     *
     * @var array
     */
    public $fillable = [
        'user_id',
        'file_id',
        'ip',
    ];

    /**
     * A file download has a download date
     *
     * @var array
     */
    public $dates = [
        'created_at'
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
            $model->id = (string) Uuid::uuid4();
            $model->created_at = now();
        });
    }

    /**
     * User that downloaded this file
     *
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * File the user downloaded
     *
     * @return BelongsTo
     */
    public function file(): Relation
    {
        return $this->belongsTo(File::class);
    }
}
