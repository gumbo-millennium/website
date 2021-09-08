<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

/**
 * Redirect instructions, used for gumbo.nu and other domains.
 *
 * @property int $id
 * @property string $slug
 * @property string $path
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction newQuery()
 * @method static \Illuminate\Database\Query\Builder|RedirectInstruction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction query()
 * @method static \Illuminate\Database\Query\Builder|RedirectInstruction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|RedirectInstruction withoutTrashed()
 * @mixin \Eloquent
 */
class RedirectInstruction extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'path',
    ];

    /**
     * Ensure the slug starts with a slash.
     */
    public function setSlugAttribute(?string $slug): void
    {
        $this->attributes['slug'] = Str::start(trim($slug, '/'), '/');
    }

    /**
     * Ensure the path is absolute.
     */
    public function setPathAttribute(?string $path): void
    {
        $this->attributes['path'] = URL::to($path);
    }
}
