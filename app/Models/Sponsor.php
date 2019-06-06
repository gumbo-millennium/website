<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Gumbo Millennium sponsors
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Sponsor extends SluggableModel
{
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
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    /**
     * Generate the slug based on the display_title property
     *
     * @return array
     */
    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'display_title',
                'unique' => true,
                'onUpdate' => true,
                'reserved' => ['add']
            ]
        ];
    }

    /**
     * Returns sponsors that are available right now
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeAvailable(Builder $builder) : Builder
    {
        return $builder
            ->whereNotNull('image_url')
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
