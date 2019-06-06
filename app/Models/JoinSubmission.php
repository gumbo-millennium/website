<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;

/**
 * Encrypted submission to Gumbo
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinSubmission extends Model
{
    // Use encryption helper
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
    ];
}
