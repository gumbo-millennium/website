<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Model
{
    protected $casts = [
        'confirmed_at' => 'date',
        'refunded_at' => 'date',
        'amount' => 'int',
        'data' => 'collection',
        'refund_amount' => 'int',
    ];

    /**
     * Returns if the payment was refunded
     *
     * @return bool
     */
    public function isCompleted() : bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Returns if the payment was refunded
     *
     * @return bool
     */
    public function isRefunded() : bool
    {
        return $this->refunded_at !== null;
    }

    /**
     * Returns true if the whole transaction was refunded
     *
     * @return bool
     */
    public function isFullyRefunded() : bool
    {
        return $this->amount === $this->refund_amount
            && $this->amount > 0;
    }

    /**
     * Returns the transaction with the given ID and provider
     *
     * @param Builder $query
     * @param string $provider
     * @param string $transactionId
     * @return Builder
     */
    public function scopeTransactionId(Builder $query, string $provider, string $transactionId) : Builder
    {
        return $query->where([
            'provider' => $provider,
            'provider_id' => $transactionId,
        ]);
    }

    /**
     * Scoped by provider
     *
     * @param Builder $query
     * @param string $provider
     * @return Builder
     */
    public function scopeProvider(Builder $query, string $provider) : Builder
    {
        return $query->where('provider', '=', $provider);
    }

    /**
     * Scopes the query to only return completed payments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query) : Builder
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scopes the query to only show refunded payments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRefunded(Builder $query) : Builder
    {
        return $query->whereNotNull('refunded_at');
    }
}
