<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use Notifiable;
    use HasRoles;
    use SoftDeletes;
    use MustVerifyEmail;

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
        'alias',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'email_verified_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'insert' => null,
        'alias' => null,
    ];

    /**
     * Returns files the user has uploaded
     *
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'owner_id');
    }

    /**
     * Returns downloads the user has performed
     *
     * @return BelongsToMany
     */
    public function downloads(): Relation
    {
        return $this->belongsToMany(File::class, 'file_downloads')
            ->as('download')
            ->using(FileDownload::class);
    }

    /**
     * Returns enrollments the user has performed
     *
     * @return HasMany
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Returns the activities the user is enrolled in
     *
     * @return Relation
     */
    public function activities(): Relation
    {
        return $this->hasManyThrough(Activity::class, Enrollment::class);
    }

    /**
     * Returns activities the user can manage
     *
     * @return Relation
     */
    public function hostedActivities(): Relation
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Returns full name of the user
     *
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        $name = collect([
            $this->first_name,
            $this->insert,
            $this->last_name
        ])->filter()->implode(' ');

        return $name !== '' ? $name : null;
    }

    /**
     * Returns the public name of the user
     *
     * @return string|null
     */
    public function getPublicNameAttribute(): ?string
    {
        return $this->alias ?? $this->name;
    }

    /**
     * Returns a list of IDs that the user hosts
     *
     * @return Collection
     */
    public function getHostedActivityIdsAttribute(array $attributes = null): iterable
    {
        // Run query
        $query = $this->getHostedActivityQuery(Activity::query());

        // Handle query
        return $attributes ? $query->only($attributes) : $query->get();
    }

    /**
     * Returns (sub)query that only returns the Activities this user
     * is a manager of
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostedActivityQuery(Builder $query): Builder
    {
        // Save $this as $user for child
        $user = $this;

        // Return data as a subquery
        return $query->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereIn('role_id', $user->roles()->pluck('id'));
        });
    }
}
