<?php

namespace App;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
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
    use Notifiable, HasRoles, SoftDeletes;

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
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * A user might've uploaded files
     *
     * @return HasMany
     */
    public function files() : HasMany
    {
        return $this->hasMany(File::class, 'owner_id');
    }

    /**
     * A user can download files
     *
     * @return BelongsToMany
     */
    public function downloads() : BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_downloads')
            ->as('download')
            ->using(FileDownload::class);
    }

    /**
     * Tries to find the WordPress user this user owns.
     *
     * @return HasOne
     */
    public function wordpress() : HasOne
    {
        return $this->hasOne(CorcelUser::class, 'user_login', 'wordpress_username');
    }

    /**
     * Returns true if the WordPress user link was modified
     *
     * @return bool
     */
    public function wordpressWasChanged() : bool
    {
        return in_array('wordpress_userid', $this->getDirty());
    }

    /**
     * Returns full name of the user
     *
     * @return string|null
     */
    public function getNameAttribute() : ?string
    {
        $name = implode(' ', array_filter([
            $this->first_name,
            $this->insert,
            $this->last_name
        ]));

        return $name !== '' ? $name : null;
    }
}
