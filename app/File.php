<?php

namespace App;

use App\FileCategory;
use App\User;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A user-uploaded file
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class File extends SluggableModel
{
    /**
     * Storage directory of files
     */
    const STORAGE_DIR = 'files';

    /**
     * {@inheritDoc}
     */
    protected $appends = [
        'url',
        'display_title'
    ];

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'title',
        'filename',
        'filesize',
        'mime',
        'path',
        'public'
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'file_meta' => 'array'
    ];

    /**
     * Generate the slug based on the display_title property
     *
     * @return array
     */
    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'display_title',
                'unique' => true,
                'onUpdate' => true
            ]
        ];
    }

    /**
     * The roles that belong to the user.
     *
     * @return Relation
     */
    public function categories() : Relation
    {
        return $this->belongsToMany(FileCategory::class, 'file_category_catalog', 'file_id', 'category_id');
    }

    /**
     * A file has an owner
     *
     * @return Relation
     */
    public function owner() : Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A file may have downloads
     *
     * @return Relation
     */
    public function download() : Relation
    {
        return $this->belongsToMany(User::class, 'file_downloads')
            ->as('download')
            ->using(FileDownload::class);
    }

    /**
     * Returns the display title of a file, or null if unknown
     *
     * @return string|null
     */
    public function getDisplayTitleAttribute() : ?string
    {
        return !empty($this->title) ? $this->title : $this->filename;
    }

    /**
     * Prevents deletion after 48hrs of uploading.
     *
     * @return bool
     */
    public function getCanDeleteAttribute() : bool
    {
        // Always allow deletion of non-created files
        if ($this->created_at === null) {
            return true;
        }

        // Check category for

        // Get a timestamp 2 days back
        $twoDaysAgo = today()->subDays(2);

        // Allow deletion if not yet saved OR if created less than 2 days ago
        return $this->created_at === null  || $this->created_at >= $twoDaysAgo;
    }

    public function getUrlAttribute() : ?string
    {
        // Ignore if slugless
        if ($this->slug === null) {
            return null;
        }

        return route('files.show', ['file' => $this]);
    }
}
