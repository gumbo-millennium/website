<?php
declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FileCategory extends SluggableModel
{
    /**
     * Find the default category
     *
     * @param array $columns
     * @return Model|Collection
     */
    public static function findDefault(array $columns = ['*'])
    {
        return static::where(['default' => true])->first($columns);
    }

    /**
     * Find the default category or throw an exception.
     *
     * @param array $columns
     * @return Model|Collection
     * @throws ModelNotFoundException
     */
    public static function findDefaultOrFail(array $columns = ['*'])
    {
        return static::where(['default' => true])->firstOrFail($columns);
    }

    public $timestamps = false;

    protected $fillable = ['title', 'default'];

    protected $casts = [
        'default' => 'bool'
    ];

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
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'file_category_catalog', 'category_id', 'file_id');
    }
}
