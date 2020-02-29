<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    /**
     * The relationship counts that should be eager loaded on every query.
     * @var array
     */
    protected $withCount = [
        'downloads'
    ];

    /**
     * Returns the downloads
     * @return HasMany
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(FileDownload::class, 'media_id');
    }
}
