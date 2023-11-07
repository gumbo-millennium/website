<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Indicates that this resource has responsible users,
 * and tracks who made changes to it.
 *
 * @property null|int $created_by_id
 * @property null|int $updated_by_id
 * @property null|User $created_by User that created this resource.
 * @property null|User $updated_by User that most recently updated this resource.
 */
trait HasResponsibleUsers
{
    public static function bootHasResponsibleUsers(): void
    {
        static::creating(function ($model) {
            if ($user = Auth::user()) {
                $model->created_by()->associate($user);
            }
        });

        static::saving(function ($model) {
            if ($user = Auth::user()) {
                $model->updated_by()->associate($user);
            }
        });
    }

    /**
     * User that created this resource.
     * @return BelongsTo<User>
     */
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * User that most recently updated this resource.
     * @return BelongsTo<User>
     */
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
