<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A referral by a user, managed by the Intro Committee.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property string $subject
 * @property string $referred_by
 * @property null|int $user_id
 * @property-read null|\App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|MemberReferral newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MemberReferral newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MemberReferral query()
 * @mixin \Eloquent
 */
class MemberReferral extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'referred_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
