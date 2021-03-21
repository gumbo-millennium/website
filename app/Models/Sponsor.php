<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasEditorJsContent;
use App\Models\Traits\HasSimplePaperclippedMedia;
use App\Traits\HasPaperclip;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Gumbo Millennium sponsors
 *
 * @property-read AttachmentInterface $backdrop
 * @property int $id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property \Illuminate\Support\Date|null $deleted_at
 * @property string $name Sponsor name
 * @property string $slug
 * @property string $url URL of sponsor landing page
 * @property \Illuminate\Support\Date|null $starts_at
 * @property \Illuminate\Support\Date|null $ends_at
 * @property int|null $has_page
 * @property int $view_count Number of showings
 * @property string|null $backdrop_file_name backdrop name
 * @property int|null $backdrop_file_size backdrop size (in bytes)
 * @property string|null $backdrop_content_type backdrop content type
 * @property string|null $backdrop_updated_at backdrop update timestamp
 * @property mixed|null $backdrop_variants backdrop variants (json)
 * @property string|null $caption
 * @property string|null $logo_gray
 * @property string|null $logo_color
 * @property string|null $contents_title
 * @property mixed|null $contents
 * @property-read \Illuminate\Database\Eloquent\Collection<SponsorClick> $clicks
 * @property-read mixed $click_count
 * @property-read string|null $content_html
 * @property-read bool $is_active
 * @property-read bool $is_classic
 * @property-read string|null $logo_color_url
 * @property-read string|null $logo_gray_url
 */
class Sponsor extends SluggableModel implements AttachableInterface
{
    use HasPaperclip;
    use HasSimplePaperclippedMedia;
    use PaperclipTrait;
    use HasEditorJsContent;
    use SoftDeletes;

    public const LOGO_DISK = 'public';
    public const LOGO_PATH = 'sponsors/logos';

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'contents' => 'null',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'view_count' => 'int',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'starts_at',
        'ends_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
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
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
                'onUpdate' => false,
            ],
        ];
    }

    /**
     * Returns sponsors that are available right now
     *
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
     *
     * @return bool
     */
    public function getIsClassicAttribute(): bool
    {
        return !$this->backdrop->exists()
            || empty($this->caption);
    }

    /**
     * Returns if this sponsor is active
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->starts_at < now()
            && ($this->ends_at === null || $this->ends_at > now());
    }

    /**
     * Returns URL to the grayscale (currentColor) logo
     *
     * @return string|null
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
     *
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function getLogoColorUrlAttribute(): ?string
    {
        if (!$this->logo_color) {
            return null;
        }
        return Storage::disk(self::LOGO_DISK)->url($this->logo_color);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(SponsorClick::class);
    }

    /**
     * Returns the number of clicks
     *
     * @return mixed
     */
    public function getClickCountAttribute()
    {
        return $this->clicks()->sum('count');
    }

    /**
     * Converts contents to HTML
     *
     * @return string|null
     */
    public function getContentHtmlAttribute(): ?string
    {
        return $this->convertToHtml($this->contents);
    }

    /**
     * Binds paperclip files
     *
     * @return void
     */
    protected function bindPaperclip(): void
    {
        // Sizes
        $this->createSimplePaperclip('backdrop', [
            'banner' => [1920, 960, true],
        ]);
    }
}
