<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasSimplePaperclippedMedia;
use App\Traits\HasPaperclip;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Gumbo Millennium sponsors
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 * @property-read AttachmentInterface $logo
 * @property-read AttachmentInterface $image
 */
class Sponsor extends SluggableModel implements AttachableInterface
{
    use HasPaperclip;
    use HasSimplePaperclippedMedia;
    use PaperclipTrait;
    use SoftDeletes;

    public const LOGO_DISK = 'public';
    public const LOGO_PATH = 'sponsors/logos';

    /**
     * The model's attributes.
     * @var array
     */
    protected $attributes = [
        'contents' => 'null',
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'view_count' => 'int',
        'click_count' => 'int',
        'contents' => 'json'
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = [
        'starts_at',
        'ends_at',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'start_at',
        'ends_at',
        'caption',
        'logo_gray',
        'logo_color',
        'contents',
    ];

    /**
     * Generate the slug based on the display_title property
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
                'onUpdate' => false
            ]
        ];
    }

    /**
     * Returns sponsors that are available right now
     * @param Builder $builder
     * @return Builder
     */
    public function scopeWhereAvailable(Builder $builder): Builder
    {
        return $builder
            ->whereNotNull('image_file_name')
            ->where(static function ($query) {
                $query->where('starts_at', '>=', now())
                    ->orWhereNull('starts_at');
            })
            ->where(static function ($query) {
                $query->where('ends_at', '<', now())
                    ->orWhereNull('ends_at');
            });
    }

    /**
     * Binds paperclip files
     * @return void
     */
    protected function bindPaperclip(): void
    {
        // Sizes
        $this->createSimplePaperclip('backdrop', [
            'banner' => [1920, 960, true]
        ]);
    }
}
