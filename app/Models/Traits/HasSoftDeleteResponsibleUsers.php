<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Indicates that this resource has responsible users for
 * soft deletes, and tracks who trashed it.
 *
 * @property null|int $deleted_by_id
 * @property null|User $deleted_by User that most recently updated this resource.
 */
trait HasSoftDeleteResponsibleUsers
{
    public static function bootHasSoftDeleteResponsibleUsers(): void
    {
        static::softDeleted(function (self $model) {
            if ($user = Auth::user()) {
                $model->deleted_by()->associate($user);
                $model->save();
            }
        });

        static::restoring(function (self $model) {
            $model->deleted_by()->disassociate();
        });
    }

    /**
     * User that trashed this resource.
     * @return BelongsTo<User>
     */
    public function deleted_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_id');
    }
}
