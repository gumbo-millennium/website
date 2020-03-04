<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\Models\Media;

/**
 * An individual file download, logs the bundle downloaded, the specific file downloaded (if any),
 * the user, the timestamp, the IP from which the user downloaded and the user agent used.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileDownload extends Pivot
{
    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        // Forward first
        parent::boot();

        // Generate UUID on create and add downloaded_at
        self::creating(static function ($model) {
            $model->id = (string) Uuid::uuid4();
            $model->created_at = now();
        });
    }

    /**
     * Categories don't have timestamps.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Disable incrementing primary key
     * @var bool
     */
    public $incrementing = false;

    /**
     * Set key type to string
     * @var string
     */
    public $keyType = 'string';

    /**
     * The IP is fillable, others aren't
     * @var array
     */
    public $fillable = [
        'user_id',
        'bundle_id',
        'media_id',
        'ip',
        'user_agent'
    ];

    /**
     * A file download has a download date
     * @var array
     */
    public $dates = [
        'created_at'
    ];

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'file_downloads';

    /**
     * User that downloaded this file
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bundle the media came from downloaded
     * @return BelongsTo
     */
    public function bundle(): Relation
    {
        return $this->belongsTo(FileBundle::class);
    }

    /**
     * Media file the user downloaded
     * @return BelongsTo
     */
    public function media(): Relation
    {
        return $this->belongsTo(Media::class);
    }
}
