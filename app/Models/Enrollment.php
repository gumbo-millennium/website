<?php

namespace App\Models;

use AustinHeap\Database\Encryption\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Relations\Relation;

class Enrollment extends UuidModel
{
    // Use encryption helper to protect user details
    use HasEncryptedAttributes;

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
        'data' => 'collection'
    ];

    /**
     * An enrollment can have multiple payments (in case one failed, for example)
     *
     * @return Relation
     */
    public function payments() : Relation
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The user this enrollment belongs to
     *
     * @return Relation
     */
    public function user() : Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The activity this enrollment belongs to
     *
     * @return Relation
     */
    public function activity() : Relation
    {
        return $this->belongsTo(Activity::class);
    }
}
