<?php

namespace App\Models;

use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A user enrollment for an activity. Optionally has payments.
 */
class Enrollment extends UuidModel
{
    // Use encryption helper to protect user details
    use HasEncryptedAttributes;

    // Allow soft-deletion (prevents re-enrollment on paid activities)
    use SoftDeletes;

    /**
     * @inheritDoc
     */
    protected $encrypted = [
        'data',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'data' => 'collection',
        'paid' => 'bool'
    ];

    /**
     * @inheritDoc
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Create unsaved enrollment
     *
     * @param User $user
     * @param Activity $activity
     * @return Enrollment
     */
    public static function enroll(User $user, Activity $activity): Enrollment
    {
        // Make empty enrollment
        $enroll = new self();

        // Assign user and activity
        $enroll->user()->associate($user);
        $enroll->activity()->associate($activity);

        // Return it
        return $enroll;
    }

    /**
     * An enrollment can have multiple payments (in case one failed, for example)
     *
     * @return HasMany
     */
    public function payments(): Relation
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The user this enrollment belongs to
     *
     * @return BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The activity this enrollment belongs to
     *
     * @return BelongsTo
     */
    public function activity(): Relation
    {
        return $this->belongsTo(Activity::class);
    }
}
