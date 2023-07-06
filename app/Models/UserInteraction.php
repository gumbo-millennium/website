<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class UserInteraction extends Model
{
    use Prunable;

    protected $timestamps = false;

    protected $casts = [
        'first_interaction' => 'datetime',
        'last_interaction' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'interaction',
        'model_id',
        'model_type',
    ];

    public static booted(): void
    {
        super::booted();

        // Ensure first and last interaction are always set.
        static::saving(function (self $interaction) {
            $interaction->first_interaction ??= Date::now();
            $interaction->last_interaction = Date::now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function scopeWhereUser(Builder $query, User $user): void
    {
        $query->whereHas('user', fn (Builder $query) => $query->whereKey($user->getKey()));
    }

    public function scopeWhereModel(Builder $query, ?Model $model): void
    {
        if ($model === null) {
            $query->doesntHave('model');
            return;
        }

        $query->whereHasMorph('model', $model::class, fn (Builder $query) => $query->whereKey($model->getKey()));
    }

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return static::where('last_interaction', '<=', Date::now()->startOfDay()->subDays(90));
    }
}
