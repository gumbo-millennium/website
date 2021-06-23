<?php

declare(strict_types=1);

namespace App\Models;

use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Encrypted submission to Gumbo.
 *
 * @property int $id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property null|string $first_name
 * @property null|string $insert
 * @property string $last_name
 * @property string $email
 * @property null|string $phone Encrypted phone number
 * @property null|string $date_of_birth Encrypted date of birth, as dd-mm-yyyy
 * @property null|string $gender User supplied gender
 * @property null|string $street Encrypted street name
 * @property null|string $number Encrypted number
 * @property null|string $city Encrypted city
 * @property null|string $postal_code Encrypted zipcode
 * @property null|string $country Encrypted country
 * @property bool $windesheim_student
 * @property bool $newsletter
 * @property null|bool $granted
 * @property null|string $referrer
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
     * Default values.
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
     * Create a MemberReferral if the submisison has a referral set.
     */
    public static function booted(): void
    {
        static::created(static function (self $submission) {
            if (! $submission->referrer) {
                return;
            }

            MemberReferral::create([
                'subject' => $submission->first_name,
                'referred_by' => $submission->referrer,
            ]);
        });
    }

    /**
     * Full name property.
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
