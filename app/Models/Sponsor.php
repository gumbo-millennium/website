<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasSimplePaperclippedMedia;
use App\Traits\HasPaperclip;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

/**
 * Gumbo Millennium sponsors
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 * @property-read AttachmentInterface $backdrop
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
            // Require logos
            ->whereNotNull('logo_color')
            ->whereNotNull('logo_gray')

            // Require an URL to be set
            ->whereNotNull('url')

            // Require to have started, and not ended yet
            ->where('starts_at', '<', now())
            ->where(static function ($query) {
                $query->where('ends_at', '>', now())
                    ->orWhereNull('ends_at');
            });
    }

    /**
     * Returns if this should be a classic view
     * @return bool
     */
    public function getIsClassicAttribute(): bool
    {
        return !$this->backdrop->exists()
            || empty($this->caption);
    }

    /**
     * Returns if this sponsor is active
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->starts_at < now()
            && ($this->ends_at === null || $this->ends_at > now());
    }

    /**
     * Returns URL to the grayscale (currentColor) logo
     * @return null|string
     * @throws InvalidArgumentException
     */
    public function getLogoGrayUrlAttribute(): ?string
    {
        if (!$this->logo_gray) {
            return null;
        }
        return Storage::disk(self::LOGO_DISK)->url($this->logo_gray);
    }

    /**
     * Returns URL to the full color logo
     * @return null|string
     * @throws InvalidArgumentException
     */
    public function getLogoColorUrlAttribute(): ?string
    {
        if (!$this->logo_color) {
            return null;
        }
        return Storage::disk(self::LOGO_DISK)->url($this->logo_color);
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
