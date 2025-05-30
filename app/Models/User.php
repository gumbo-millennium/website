<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Arr;
use App\Models\Shop\Order;
use App\Notifications\VerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User.
 *
 * @property int $id
 * @property null|\Illuminate\Support\Carbon $created_at
 * @property null|\Illuminate\Support\Carbon $updated_at
 * @property null|\Illuminate\Support\Carbon $deleted_at
 * @property null|string $stripe_id
 * @property null|int $conscribo_id
 * @property null|string $telegram_id
 * @property string $first_name
 * @property null|string $insert
 * @property string $last_name
 * @property null|string $name
 * @property string $email
 * @property null|\Illuminate\Support\Carbon $email_verified_at
 * @property string $password
 * @property null|string $remember_token
 * @property null|\Illuminate\Support\Carbon $last_seen_at
 * @property null|string $alias
 * @property Collection $grants
 * @property null|string $gender
 * @property null|Collection $address
 * @property null|string $phone
 * @property bool $locked
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FileDownload> $downloads
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Enrollment> $enrollments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FileBundle> $files
 * @property-read null|\Illuminate\Support\HtmlString $address_string
 * @property-read Collection $hosted_activity_ids
 * @property-read bool $is_member
 * @property-read string $leaderboard_name
 * @property-read null|string $public_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $hostedActivities
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions, $without = false)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null, $without = false)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutPermission($permissions)
 * @method static Builder|User withoutRole($roles, $guard = null)
 * @method static Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use MustVerifyEmail;
    use Notifiable;
    use SoftDeletes;

    public const SHOW_IN_LEADERBOARD_GRANT = 'leaderboard:show';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'insert',
        'last_name',
        'email',
        'password',
        'last_seen_at',
        'alias',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'conscribo_id',
        'password',
        'remember_token',
        'last_seen_at',
        'phone',
        'address',
        'locked',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'insert' => null,
        'alias' => null,
        'grants' => '[]',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'conscribo_id' => 'int',
        'address' => 'encrypted:collection',
        'grants' => 'collection',
        'deleted_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'locked' => 'boolean',
    ];

    /**
     * Returns files the user has uploaded.
     */
    public function files(): HasMany
    {
        return $this->hasMany(FileBundle::class, 'owner_id');
    }

    /**
     * Returns downloads the user has performed.
     *
     * @return HasMany
     */
    public function downloads(): Relation
    {
        return $this->hasMany(FileDownload::class, 'user_id');
    }

    /**
     * Returns enrollments the user has performed.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Returns the activities the user is enrolled in.
     *
     * @return HasManyThrough
     */
    public function activities(): Relation
    {
        return $this->hasManyThrough(Activity::class, Enrollment::class);
    }

    /**
     * Returns activities the user can manage.
     *
     * @return HasMany
     */
    public function hostedActivities(): Relation
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Returns the shop orders.
     * @return HasMany
     */
    public function orders(): Relation
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Returns the public name of the user.
     */
    public function getPublicNameAttribute(): ?string
    {
        return $this->alias ?? $this->first_name;
    }

    /**
     * Returns if this user is a member.
     */
    public function getIsMemberAttribute(): bool
    {
        return $this->hasRole('member');
    }

    /**
     * Retuns if this user account is locked, and cannot login.
     */
    public function isLocked(): bool
    {
        return (bool) $this->locked;
    }

    /**
     * Returns a list of IDs that the user hosts.
     *
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
     *
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
     * Returns a subquery to select all activity IDs this user can manage.
     *
     * @throws InvalidArgumentException
     */
    public function getHostedActivityIdQuery(): Builder
    {
        return $this->getHostedActivityQuery()->select('id');
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Sets a grant on the user.
     */
    public function setGrant(string $key, ?bool $granted): self
    {
        $this->grants = Collection::make($this->grants)
            ->put($key, $granted)
            ->reject(fn ($value) => $value === null)
            ->sort();

        return $this;
    }

    /**
     * Returns true if this user has granted the given flag.
     */
    public function hasGrant(string $key, bool $default = false): bool
    {
        return (bool) $this->grants?->get($key, $default);
    }

    /**
     * Name to show on the leaderboard, might be blurred.
     */
    public function getLeaderboardNameAttribute(): string
    {
        $userName = $this->alias ?? $this->first_name;
        if ($this->hasGrant(self::SHOW_IN_LEADERBOARD_GRANT)) {
            return $userName;
        }

        return preg_replace('/[^\s-]/', '*', $userName);
    }

    public function getAddressStringAttribute(): ?HtmlString
    {
        $line1 = Arr::get($this->address, 'line1');
        $line2 = Arr::get($this->address, 'line2');
        $postcode = Arr::get($this->address, 'postal_code');
        $city = Arr::get($this->address, 'city');

        if (! $line1 || ! $city) {
            return null;
        }

        $lines = [
            $line1,
            $line2,
            "{$postcode}, {$city}",
        ];

        $address = collect($lines)
            ->filter(fn ($value) => ! empty($value))
            ->map(fn ($value) => e($value))
            ->implode('<br />');

        return new HtmlString($address);
    }
}
