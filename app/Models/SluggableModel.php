<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\IsSluggable;
use Illuminate\Database\Eloquent\Model;

/**
 * A model that has a slug property, which is used to generate unique
 * looking URLs.
 */
abstract class SluggableModel extends Model
{
    use IsSluggable;
}
