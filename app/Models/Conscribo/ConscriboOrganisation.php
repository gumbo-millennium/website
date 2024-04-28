<?php

declare(strict_types=1);

namespace App\Models\Conscribo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An organisation from Conscribo. These are usually sponsors, but can also be
 * other organisations used for invoicing.
 *
 * @property int $id
 * @property int $conscribo_id
 * @property string $name
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|string $contract_ends_at
 * @method static \Database\Factories\Conscribo\ConscriboOrganisationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ConscriboOrganisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConscriboOrganisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConscriboOrganisation query()
 * @mixin \Eloquent
 */
class ConscriboOrganisation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'conscribo_id',
        'name',
        'contract_ends_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'contract_ends_at' => 'datetime',
        ];
    }
}
