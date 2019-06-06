<?php
declare(strict_types=1);

namespace App\Models;

use Corcel\Model\Option as CorcelOption;

/**
 * WordPress option with more features
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Option extends CorcelOption
{
    /**
     * Key containing the secret for authentication
     *
     * @var string
     */
    const OPTION_AUTH_TOKEN = 'wordpress-auth-secret';

    /**
     * Updates or creates the new key
     * @param string $key
     * @param mixed $value
     * @return Option
     */
    public static function change($key, $value)
    {
        $option = CorcelOption::firstOrNew(['option_name' => $key]);
        $option->option_value = is_array($value) ? serialize($value) : $value;
        $option->save();
    }
}
