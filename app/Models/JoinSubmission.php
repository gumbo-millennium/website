<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\EncryptedDateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Date;

/**
 * App\Models\JoinSubmission.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|string $first_name
 * @property null|string $insert
 * @property string $last_name
 * @property string $email
 * @property null|mixed $phone Encrypted phone number
 * @property null|\Illuminate\Support\Carbon $date_of_birth Encrypted date of birth, as dd-mm-yyyy
 * @property null|string $gender User supplied gender
 * @property null|mixed $street Encrypted street name
 * @property null|mixed $number Encrypted number
 * @property null|mixed $city Encrypted city
 * @property null|mixed $postal_code Encrypted zipcode
 * @property null|mixed $country Encrypted country
 * @property int $windesheim_student
 * @property int $newsletter
 * @property null|int $granted
 * @property null|string $referrer
 * @property-read string $name
 * @method static \Database\Factories\JoinSubmissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|JoinSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JoinSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JoinSubmission query()
 * @mixin \Eloquent
 */
class JoinSubmission extends Model
{
    use HasFactory;
    use Prunable;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'phone' => 'encrypted',
        'date_of_birth' => EncryptedDateCast::class,
        'street' => 'encrypted',
        'number' => 'encrypted',
        'city' => 'encrypted',
        'postal_code' => 'encrypted',
        'country' => 'encrypted',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
     * The model's attributes.
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

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return $this->query()
            ->where('created_at', '<', Date::today()->subMonths(6));
    }
}
