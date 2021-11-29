<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Spatie\MediaLibrary\Models\Media;

/**
 * App\Models\FileDownload.
 *
 * @property string $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property int $user_id
 * @property int $bundle_id
 * @property null|int $media_id
 * @property string $ip
 * @property string $user_agent
 * @property-read \App\Models\FileBundle $bundle
 * @property-read null|Media $media
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|FileDownload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileDownload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileDownload query()
 * @mixin \Eloquent
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
     * Disable incrementing primary key.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Set key type to string.
     *
     * @var string
     */
    public $keyType = 'string';

    /**
     * The IP is fillable, others aren't.
     *
     * @var array
     */
    public $fillable = [
        'user_id',
        'bundle_id',
        'media_id',
        'ip',
        'user_agent',
    ];

    /**
     * A file download has a download date.
     *
     * @var array
     */
    public $dates = [
        'created_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_downloads';

    /**
     *  Setup model event hooks.
     */
    public static function boot()
    {
        // Forward first
        parent::boot();

        // Generate UUID on create and add downloaded_at
        self::creating(function (self $download) {
            $download->id ??= (string) Str::uuid();
            $download->created_at = Date::now();
        });
    }

    /**
     * User that downloaded this file.
     *
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bundle the media came from downloaded.
     *
     * @return BelongsTo
     */
    public function bundle(): Relation
    {
        return $this->belongsTo(FileBundle::class);
    }

    /**
     * Media file the user downloaded.
     *
     * @return BelongsTo
     */
    public function media(): Relation
    {
        return $this->belongsTo(Media::class);
    }
}
