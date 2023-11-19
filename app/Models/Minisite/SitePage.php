<?php

declare(strict_types=1);

namespace App\Models\Minisite;

use Advoor\NovaEditorJs\NovaEditorJsCast;
use App\Enums\Models\Minisite\PageType;
use App\Models\Traits\HasResponsibleUsers;
use App\Models\Traits\HasSoftDeleteResponsibleUsers;
use App\Models\Traits\IsSluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;

/**
 * App\Models\Minisite\SitePage.
 *
 * @property int $id
 * @property int $site_id
 * @property PageType $type
 * @property string $title
 * @property string $slug
 * @property bool $visible
 * @property null|null|\Advoor\NovaEditorJs\NovaEditorJsData $contents
 * @property null|string $cover
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property null|int $created_by_id
 * @property null|int $updated_by_id
 * @property null|int $deleted_by_id
 * @property-read null|\App\Models\User $created_by
 * @property-read null|\App\Models\User $deleted_by
 * @property-read null|\Illuminate\Support\HtmlString $html
 * @property-read string $url
 * @property-read \App\Models\Minisite\Site $site
 * @property-read null|\App\Models\User $updated_by
 * @method static \Database\Factories\Minisite\SitePageFactory factory(...$parameters)
 * @method static Builder|SitePage findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|SitePage newModelQuery()
 * @method static Builder|SitePage newQuery()
 * @method static Builder|SitePage onlyTrashed()
 * @method static Builder|SitePage query()
 * @method static Builder|SitePage whereSite(\App\Models\Minisite\Site|string $site)
 * @method static Builder|SitePage whereSlug(string $slug)
 * @method static Builder|SitePage withTrashed()
 * @method static Builder|SitePage withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, $attribute, $config, $slug)
 * @method static Builder|SitePage withoutTrashed()
 * @mixin \Eloquent
 */
class SitePage extends Model
{
    use HasFactory;
    use HasResponsibleUsers;
    use HasSoftDeleteResponsibleUsers;
    use IsSluggable;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'minisite_pages';

    /**
     * The model's attributes.
     */
    protected $attributes = [
        'contents' => '{"time": 0, "blocks": [], "version": "1.0.0"}',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'type' => PageType::class,
        'visible' => 'bool',
        'contents' => NovaEditorJsCast::class,
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'site',
        'type',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'contents',
        'cover',
    ];

    /**
     * Returns a query for the given site, optionally for a specific page.
     * @return Builder<SitePage>
     */
    public static function querySite(string $site, ?string $page = null): Builder
    {
        return self::query()
            ->whereSite($site)
            ->when($page, fn (Builder $query) => $query->where('slug', $page));
    }

    /**
     * Site this page belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Allows for easy scoping to the site domain name or the site object.
     */
    public function scopeWhereSite(Builder $query, string|Site $site): void
    {
        if (is_string($site)) {
            $query->whereHas('site', fn (Builder $query) => $query->where('domain', $site));

            return;
        }

        $query->where('site_id', $site->id);
    }

    /**
     * Generate the slug based on the title property.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'includeTrashed' => true,
            ],
        ];
    }

    /**
     * Converts contents to HTML.
     */
    public function getHtmlAttribute(): ?HtmlString
    {
        return $this->contents?->toHtml();
    }

    /**
     * Returns the URL to this page.
     *
     * @throws LogicException
     */
    public function getUrlAttribute(): string
    {
        $this->loadMissing('site');

        if ($this->site === null) {
            return "/{$this->slug}";
        }

        return "https://{$this->site->domain}/{$this->slug}";
    }

    /**
     * @inheritDoc
     */
    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model, $attribute, $config, $slug): Builder
    {
        return $query->where('site_id', $model->site_id);
    }
}
