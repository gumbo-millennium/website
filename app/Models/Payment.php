<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Payment extends UuidModel
{
    /**
     * @inheritDoc
     */
    protected $dates = [
        'completed_at',
        'refunded_at',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'amount' => 'int',
        'refunded_amount' => 'int',
        'data' => 'collection',
    ];

    /**
     * Returns if the payment was refunded
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Returns if the payment was refunded
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }

    /**
     * Returns true if the whole transaction was refunded
     *
     * @return bool
     */
    public function isFullyRefunded(): bool
    {
        return $this->amount === $this->refund_amount
            && $this->amount > 0;
    }

    /**
     * Scopes the query to only return completed payments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scopes the query to only show refunded payments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->whereNotNull('refunded_at');
    }
}
