<?php

declare(strict_types=1);

namespace App\Models\Conscribo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

/**
 * A committee in Conscribo.
 *
 * @property int $id
 * @property int $conscribo_id
 * @property string $name
 * @property string $email
 * @property mixed $aliases
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conscribo\ConscriboUser> $members
 * @method static \Illuminate\Database\Eloquent\Builder|ConscriboCommittee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConscriboCommittee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConscriboCommittee query()
 * @method static \Database\Factories\Conscribo\ConscriboCommitteeFactory factory($count = null, $state = [])
 * @mixin \Eloquent
 */
class ConscriboCommittee extends Model
{
    use HasFactory;
    use HasRoles;

    protected $attributes = [
        'aliases' => '[]',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'conscribo_id',
        'name',
        'email',
        'aliases',
    ];

    /**
     * Returns the members of this committee.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(ConscriboUser::class, 'conscribo_committee_user')
            ->using(CommitteeUser::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aliases' => 'collection',
        ];
    }
}
