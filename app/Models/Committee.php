<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Committee extends Model
{
    use HasFactory;

    protected $casts = [
        'conscribo_id' => 'int',
        'owner_id' => 'int',
        'synced_at' => 'datetime',
    ];

    protected $fillable = [
        'conscribo_id',
        'name',
        'owner_id',
        'synced_at',
    ];

    protected $visible = [
        'id',
        'name',
        'synced_at',
    ];

    protected $appends = [
        'owner',
        'users',
    ];

    public function owner(): Relation
    {
        return $this->belongsTo(User::class);
    }

    public function users(): Relation
    {
        return $this->belongsToMany(User::class);
    }
}
