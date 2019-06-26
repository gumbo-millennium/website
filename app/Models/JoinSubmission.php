<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Laravel\Nova\Actions\Actionable;

/**
 * Encrypted submission to Gumbo
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinSubmission extends Model
{
    // Use action log and encryption helper
    use Actionable, HasEncryptedAttributes;

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
    ];

    /**
     * Full name property
     *
     * @return string
     */
    public function getNameAttribute() : string
    {
        return implode(' ', array_filter([
            $this->first_name,
            $this->insert,
            $this->last_name
        ]));
    }
}
