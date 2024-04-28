<?php

declare(strict_types=1);

namespace App\Models\Conscribo;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $user_id
 * @property int $committee_id
 * @property null|string $role
 * @property int $is_owner
 * @method static \Illuminate\Database\Eloquent\Builder|CommitteeUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommitteeUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommitteeUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommitteeUser whereCommitteeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommitteeUser whereIsOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommitteeUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommitteeUser whereUserId($value)
 * @mixin \Eloquent
 */
class CommitteeUser extends Pivot
{
    //
}
