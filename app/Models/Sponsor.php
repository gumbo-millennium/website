<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPaperclip;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Gumbo Millennium sponsors
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 *
 * @property-read AttachmentInterface $logo
 * @property-read AttachmentInterface $image
 */
class Sponsor extends Model implements AttachableInterface
{
    use PaperclipTrait;
    use HasPaperclip;

    /**
     * The Sponsors default attributes.
     *
     * @var array
     */
    protected $attributes = [
        'description' => null,
        'image_url' => null,
        'logo_url' => null,
        'action' => 'Lees meer',
        'classic' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'description',
        'action',
        'image_url',
        'logo_url',
        'classic',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'classic' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'view_count' => 'int',
        'click_count' => 'int'
    ];

    /**
     * Binds paperclip files
     *
     * @return void
     */
    protected function bindPaperclip(): void
    {
        // Max sizes
        $bannerWidth = 1280 / 12 * 8;
        $bannerHeight = 120;

        // The actual screenshots
        $this->hasAttachedFile('image', [
            'variants' => [
                // Make banner-sized image
                Variant::make('banner')->steps([
                    ResizeStep::make()->width($bannerWidth)->height($bannerHeight)
                ])->extension('png'),

                // Make banner-sized image at hdpi scale
                Variant::make('banner@2x')->steps([
                    ResizeStep::make()->width($bannerWidth * 2)->height($bannerHeight * 2)
                ])->extension('png'),
            ]
        ]);
    }

    /**
     * Returns sponsors that are available right now
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeAvailable(Builder $builder): Builder
    {
        return $builder
            ->whereNotNull('image_file_name')
            ->where(function ($query) {
                $query->where('starts_at', '>=', now())
                    ->orWhereNull('starts_at');
            })
            ->where(function ($query) {
                $query->where('ends_at', '<', now())
                    ->orWhereNull('ends_at');
            });
    }
}
