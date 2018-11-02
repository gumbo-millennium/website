<?php
declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A file category, containing files
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileCategory extends SluggableModel
{
    /**
     * Find the default category
     *
     * @param array $columns
     * @return Model|Collection
     */
    public static function findDefault()
    {
        // Find first default category, or create one
        return static::firstOrCreate(['default' => true], [
            'title' => 'Overig'
        ]);
    }

    /**
     * Find the default category or throw an exception.
     *
     * @param array $columns
     * @return Model|Collection
     * @throws ModelNotFoundException
     * @deprecated findDefault creates category on the fly if missing
     */
    public static function findDefaultOrFail(array $columns = ['*'])
    {
        return static::findDefault();
    }

    /**
     * Categories don't have timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Title and default are fillable
     *
     * @var array
     */
    protected $fillable = ['title', 'default'];

    /**
     * Cast 'default' property to a bool
     *
     * @var array
     */
    protected $casts = [
        'default' => 'bool'
    ];

    // Always auto-load files
    protected $with = [
        'files'
    ];

    /**
     * Slug on the category title
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

    /**
     * The files that belong to this category
     *
     * @return Relation
     */
    public function files() : Relation
    {
        return $this->belongsToMany(File::class, 'file_category_catalog', 'category_id', 'file_id');
    }
}
