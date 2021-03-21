<?php

declare(strict_types=1);

namespace App\Models;

use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Encrypted submission to Gumbo
 *
 * @property int $id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property string|null $first_name
 * @property string|null $insert
 * @property string $last_name
 * @property string $email
 * @property string|null $phone Encrypted phone number
 * @property string|null $date_of_birth Encrypted date of birth, as dd-mm-yyyy
 * @property string|null $gender User supplied gender
 * @property string|null $street Encrypted street name
 * @property string|null $number Encrypted number
 * @property string|null $city Encrypted city
 * @property string|null $postal_code Encrypted zipcode
 * @property string|null $country Encrypted country
 * @property bool $windesheim_student
 * @property bool $newsletter
 * @property bool|null $granted
 * @property string|null $referrer
 * @property-read string $name
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
     *
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
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->insert,
            $this->last_name,
        ])->reject('empty')->implode(' ');
    }
}
