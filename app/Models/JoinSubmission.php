<?php

declare(strict_types=1);

namespace App\Models;

use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Encrypted submission to Gumbo
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinSubmission extends Model
{
    // Use action log and encryption helper
    use HasEncryptedAttributes;

    /**
     * @inheritDoc
     */
    protected $encrypted = [
        'phone',
        'date_of_birth',
        'street',
        'number',
        'city',
        'postal_code',
        'country',
    ];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'first_name',
        'insert',
        'last_name',
        'phone',
        'email',
        'date_of_birth',
        'gender',
        'street',
        'number',
        'city',
        'postal_code',
        'country',
        'windesheim_student',
        'newsletter',
        'referrer',
    ];

    /**
     * Default values
     * @var array
     */
    protected $attributes = [
        'first_name' => null,
        'insert' => null,
        'last_name' => null,
        'phone' => null,
        'email' => null,
        'date_of_birth' => null,
        'gender' => null,
        'street' => null,
        'number' => null,
        'city' => null,
        'postal_code' => null,
        'country' => null,
    ];

    /**
     * Full name property
     * @return string
     */
    public function getNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->insert,
            $this->last_name
        ])->reject('empty')->implode(' ');
    }
}
