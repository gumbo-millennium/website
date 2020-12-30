<?php

declare(strict_types=1);

namespace App\Console\Commands\Traits;

use App\Models\User;

/**
 * Finds users in the {user} argument
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait FindsUserTrait
{
    /**
     * Finds the user mentioned in the 'user' argument, either by ID, e-mail or alias
     *
     * @return User|null
     */
    protected function getUserArgument(): ?User
    {
        $query = $this->argument('user');
        if (empty($query)) {
            return null;
        }

        if (is_numeric($query)) {
            return User::find($query);
        }

        if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $query)->first();
        }

        return User::whereRaw('LOWER(alias) = LOWER(?)', [$query])->first();
    }
}
