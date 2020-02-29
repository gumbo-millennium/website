<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Arr;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use Searchable;

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

    /**
     * Get the indexable data array for the model.
     * @return array
     */
    public function toSearchableArray()
    {
        // Get basic data
        $data = Arr::only($this->toArray(), ['name', 'file_name']);

        // Add contents, if applicable
        $data['content'] = $this->getCustomProperty('file-content');

        // Return
        return $data;
    }

    /**
     * Prevent searching non-published files
     * @return bool
     */
    public function shouldBeSearchable()
    {
        $model = $this->model();
        return (!$model instanceof FileBundle) || $model->is_available;
    }
}
