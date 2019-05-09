<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Permission\Models\Role;

class Activity extends Model
{
    /**
     * @inheritDoc
     */
    protected $dates = [
        'event_start',
        'event_end',
        'enrollment_start',
        'enrollment_end'
    ];

    protected $casts = [
        // Number of seats
        'seats' => 'int',
        'public_seats' => 'int',

        // Pricing
        'price_member' => 'int',
        'price_guest' => 'int',

        // Extra information
        'enrollment_questions' => 'collection',
    ];

    /**
     * Returns the associated role
     *
     * @return Relation
     */
    public function role() : Relation
    {
        return $this->belongsTo(Role::class, 'id', 'role_id');
    }

    /**
     * Returns all enrollments (both pending and active)
     *
     * @return Relation
     */
    public function enrollments() : Relation
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Returns all made payments for this event
     *
     * @return Relation
     */
    public function payments() : Relation
    {
        return $this->hasMany(Payment::class);
    }
}
