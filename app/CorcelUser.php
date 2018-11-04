<?php

namespace App;

use Corcel\Model\User;
use Illuminate\Database\Eloquent\Model;
use Corcel\Services\PasswordService;

/**
 * Adds fillable objects to the User
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class CorcelUser extends User
{
    protected $fillable = [
        'user_login',
        'user_pass',
        'user_nicename',
        'user_email',
        'user_url',
        'user_activation_key',
        'user_status',
        'display_name'
    ];

    public function setPassword(?string $password) : self
    {
        if ($password === null) {
            $this->user_pass = '0';
        } else {
            $this->user_pass =(new PasswordService())->makeHash($password);
        }

        return $this;
    }

    /**
     * Returns a generated password, which is also assigned to the user.
     *
     * @param int $minLength Minimum password length, above 8 recommended
     * @param int $maxLength Maximum password length, above 16 recommended
     * @return string
     */
    public function generatePassword(int $minLength = 12, int $maxLength = 24) : string
    {
        // Generate password
        $password = str_random(random_int($minLength, $maxLength));

        // Set password
        $this->setPassword($password);

        // Return it too
        return $password;
    }
}
