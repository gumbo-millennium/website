<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * App\Models\ScheduledMail.
 *
 * @property int $id
 * @property string $group
 * @property string $name
 * @property \Illuminate\Support\Carbon $scheduled_for
 * @property null|\Illuminate\Support\Carbon $sent_at
 * @property bool $is_sent
 * @method static Builder|ScheduledMail newModelQuery()
 * @method static Builder|ScheduledMail newQuery()
 * @method static Builder|ScheduledMail query()
 * @method static Builder|ScheduledMail whereNotSent()
 * @method static Builder|ScheduledMail whereSent()
 * @mixin \Eloquent
 */
class ScheduledMail extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'group' => 'string',
        'name' => 'string',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group',
        'name',
        'scheduled_for',
        'sent_at',
    ];

    /**
     * Returns a ScheduledMail with the given name, optionally scoped to the user.
     *
     * @return ScheduledMail
     */
    public static function findForModelMail(Model $model, string $name, ?User $user = null): self
    {
        return static::firstOrNew([
            'group' => self::findObjectName($model),
            'name' => sprintf('%s:%s', Str::slug($name), $user ? $user->id : 'any'),
        ]);
    }

    /**
     * Finds all mails sent for the given group.
     */
    public static function findAllForModel(Model $model): Collection
    {
        return self::query()
            ->whereGroup(self::findObjectName($model))
            ->get();
    }

    private static function findObjectName(Model $model): string
    {
        return sprintf('%s:%s', \class_basename($model), $model->{$model->primaryKey});
    }

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
