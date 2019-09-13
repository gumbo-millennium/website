<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Page extends SluggableModel
{
    /**
     * Pages required to exist, cannot be deleted or renamed
     */
    public const REQUIRED_PAGES = [
        'home' => 'Homepage',
        'privacy-policy' => 'Privacy Policy',
        'terms' => 'Terms and Conditions',
        'error-404' => 'Not Found',
    ];

    public const SLUG_HOMEPAGE = 'home';
    public const SLUG_PRIVACY = 'privacy-policy';
    public const SLUG_404 = 'error-404';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'slug',
        'title',
        'contents'
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'content' => 'json',
        'user_id' => 'int',
    ];

    /**
     * Generate the slug based on the title property
     *
     * @return array
     */
    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true
            ]
        ];
    }

    public function scopeHome(Builder $query) : Builder
    {
        return $query->where('slug', 'homepage');
    }

    /**
     * Returns the owning user, if present
     *
     * @return Relation
     */
    public function author() : Relation
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }
}
