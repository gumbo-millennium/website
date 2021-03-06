<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Arr;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\Models\Media as BaseMedia;

/**
 * File collection media.
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property null|string $mime_type
 * @property string $disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $responsive_images
 * @property null|int $order_column
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<FileDownload> $downloads
 * @property-read null|\App\Models\FileBundle $bundle
 * @property-read string $extension
 * @property-read string $human_readable_size
 * @property-read string $type
 * @property-read \Illuminate\Database\Eloquent\Model $model
 */
class Media extends BaseMedia
{
    use Searchable;

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = [
        'downloads',
    ];

    /**
     * Returns the downloads.
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(FileDownload::class, 'media_id');
    }

    /**
     * Returns the file bundle this media file belongs to.
     */
    public function getBundleAttribute(): ?FileBundle
    {
        return $this->model instanceof FileBundle ? $this->model : null;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        // Get basic data
        $data = Arr::only($this->toArray(), ['id', 'name', 'file_name']);

        // Add contents, if applicable
        $data['content'] = $this->getCustomProperty('file-content');

        // Return
        return $data;
    }

    /**
     * Prevent searching non-published files.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        $model = $this->model();

        return (! $model instanceof FileBundle) || $model->is_available;
    }
}
