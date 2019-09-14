<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Traits\HasParent;

/**
 * A file category, containing files
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileCategory extends SluggableModel
{
    use HasParent;

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
                'unique' => true,
                'reserved' => ['add', 'edit', 'remove']
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
        return $this->hasMany(File::class, 'category_id', 'id');
    }

    /**
     * The files that belong to this category
     *
     * @return Relation
     */
    public function downloads() : Relation
    {
        return $this->hasManyThrough(
            FileDownload::class,
            File::class,
            'category_id', // Foreign key on files table
            'file_id', // Foreign key on downloads table
            'id', // Local key on file_categories table
            'id' // Local key on files table
        );
    }
}
