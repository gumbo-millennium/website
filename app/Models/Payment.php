<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * A payment transaction
 *
 * @property string $id
 * @property string $transaction_id
 * @property int $user_id
 * @property string $enrollment_id
 * @property \Illuminate\Support\Date $created_at
 * @property \Illuminate\Support\Date $updated_at
 * @property \Illuminate\Support\Date|null $completed_at
 * @property string $status
 * @property int $amount In cents
 * @property \Illuminate\Support\Date|null $refunded_at
 * @property int|null $refunded_amount
 * @property \Illuminate\Support\Collection|null $data
 * @property-read bool $is_completed
 * @property-read bool $is_refunded
 */
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
    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Returns if the payment was refunded
     *
     * @return bool
     */
    public function getIsRefundedAttribute(): bool
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
