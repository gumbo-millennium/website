<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduledMail extends Model
{
    /**
     * Returns a ScheduledMail with the given name
     * @param Model $model
     * @param string $name
     * @return ScheduledMail
     */
    public static function findForModelMail(Model $model, string $name): self
    {
        $objectName = sprintf("%s:%s", \class_basename($model), $model->{$model->primaryKey});
        return static::firstOrNew([
            'group' => $objectName,
            'name' => Str::slug($name)
        ]);
    }

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'group' => 'string',
        'name' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = [
        'scheduled_for',
        'sent_at',
    ];

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'group',
        'name',
        'scheduled_for',
        'sent_at'
    ];

    public function scopeWhereSent(Builder $query): Builder
    {
        return $query->whereNotNull('sent_at');
    }

    public function scopeWhereNotSent(Builder $query): Builder
    {
        return $query->whereNull('sent_at');
    }

    public function getIsSentAttribute(): bool
    {
        return $this->sent_at !== null;
    }
}
