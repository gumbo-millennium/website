<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ConvertsToStripe;
use App\Helpers\Arr;
use App\Notifications\VerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Roelofr\EncryptionCast\Casts\Compat\AustinHeapEncryptedAttribute as EncryptedAttribute;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract, ConvertsToStripe
{
    use Notifiable;
    use HasRoles;
    use SoftDeletes;
    use MustVerifyEmail;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'first_name',
        'insert',
        'last_name',
        'email',
        'password',
        'alias',
    ];

    /**
     * The attributes that should be hidden for arrays.
     * @var array
     */
    protected $hidden = [
        'stripe_id',
        'conscribo_id',
        'password',
        'remember_token',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'email_verified_at',
    ];

    /**
     * The model's default values for attributes.
     * @var array
     */
    protected $attributes = [
        'insert' => null,
        'alias' => null,
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'conscribo_id' => 'int',
        'address' => EncryptedAttribute::class . 'json',
        'phone' => EncryptedAttribute::class,
    ];

    /**
     * Returns files the user has uploaded
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(FileBundle::class, 'owner_id');
    }

    /**
     * Returns downloads the user has performed
     * @return BelongsToMany
     */
    public function downloads(): Relation
    {
        return $this->hasMany(FileDownload::class, 'user_id');
    }

    /**
     * Returns enrollments the user has performed
     * @return HasMany
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Returns the activities the user is enrolled in
     * @return HasManyThrough
     */
    public function activities(): Relation
    {
        return $this->hasManyThrough(Activity::class, Enrollment::class);
    }

    /**
     * Returns activities the user can manage
     * @return HasMany
     */
    public function hostedActivities(): Relation
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Returns the public name of the user
     * @return string|null
     */
    public function getPublicNameAttribute(): ?string
    {
        return $this->alias ?? $this->name;
    }

    /**
     * Returns if this user is a member
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsMemberAttribute(): bool
    {
        return $this->hasRole('member');
    }

    /**
     * Returns a list of IDs that the user hosts
     * @return Collection
     */
    public function getHostedActivityIdsAttribute(?array $attributes = null): iterable
    {
        // Run query
        $query = $this->getHostedActivityQuery(Activity::query());

        // Handle query
        return $attributes ? $query->only($attributes) : $query->get();
    }

    /**
     * Returns (sub)query that only returns the Activities this user
     * is a manager of.
     * @param null|Builder $query
     * @return Builder
     * @throws InvalidArgumentException
     */
    public function getHostedActivityQuery(?Builder $query = null): Builder
    {
        // Ensure a query is present
        $query ??= Activity::query();

        // Return all if the user can do anything
        if ($this->can('admin', Activity::class)) {
            return $query;
        }

        // Return data as a subquery
        return $query->where(function ($query) {
            $query->whereIn('role_id', $this->roles()->pluck('id'));
        });
    }

    /**
     * Returns a subquery to select all activity IDs this user can manage
     * @return Builder
     * @throws InvalidArgumentException
     */
    public function getHostedActivityIdQuery(): Builder
    {
        return $this->getHostedActivityQuery()->select('id');
    }

    /**
     * Send the email verification notification.
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Returns Stripe-ready array
     * @return array
     */
    public function toStripeCustomer(): array
    {
        // Build base data
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address
        ];

        // Add shipping with subset of data
        $data['shipping'] = Arr::only($data, ['name', 'phone', 'address']);

        // Remove shipping address if empty, since the Stripe API doesn't allow changing it
        if (Arr::get($data, 'shipping.address') === null) {
            Arr::forget($data, 'shipping');
        }

        // Return new data
        return $data;
    }
}
