<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Redirect instructions, used for gumbo.nu and other domains.
 *
 * @property int $id
 * @property string $slug
 * @property string $path
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RedirectInstruction whereUpdatedAt($value)
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
}
