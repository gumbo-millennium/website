<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Arr;
use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * App\Models\Media.
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property null|string $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property null|string $mime_type
 * @property string $disk
 * @property null|string $conversions_disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $responsive_images
 * @property null|int $order_column
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|array $generated_conversions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FileDownload> $downloads
 * @property-read mixed $extension
 * @property-read null|\App\Models\FileBundle $bundle
 * @property-read mixed $human_readable_size
 * @property-read Eloquent|\Illuminate\Database\Eloquent\Model $model
 * @property-read mixed $original_url
 * @property-read mixed $preview_url
 * @property-read mixed $type
 * @method static \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, static> all($columns = ['*'])
 * @method static \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media newQuery()
 * @method static Builder|Media ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|Media query()
 * @mixin Eloquent
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
