<?php

namespace App;

use App\FileCategory;
use App\User;

class File extends SluggableModel
{
    /**
     * Storage directory of files
     */
    const STORAGE_DIR = 'files';

    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'display_title',
                'unique' => true,
                'onUpdate' => true
            ]
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function categories()
    {
        return $this->belongsToMany(FileCategory::class, 'file_category_catalog', 'file_id', 'category_id');
    }

    public function owner()
    {
        return $this->hasOne(User::class);
    }

    public function getDisplayTitleAttribute() : ?string
    {
        return !empty($this->title) ? $this->title : $this->filename;
    }
}
