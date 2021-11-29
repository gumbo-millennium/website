<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

/**
 * @property-read Collection|Payment[] $payments
 * @property-read string $paymentStatus
 */
trait HasPayments
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->payments->whereNotNull('paid_at')->count()) {
            return PaymentStatus::PAID;
        }

        if ($this->payments->whereNotNull('cancelled_at')->count()) {
            return PaymentStatus::CANCELLED;
        }

        if ($this->payments->whereNotNull('expired_at')->where('expired_at', '<', Date::now())->count()) {
            return PaymentStatus::EXPIRED;
        }

        if ($this->payments->whereNotNull('transaction_id')->count()) {
            return PaymentStatus::OPEN;
        }

        return PaymentStatus::PENDING;
    }

    public function scopeWherePaid(Builder $query): void
    {
        $this->whereHas('payments', function (Builder $query) {
            $query->whereNotNull('paid_at');
        });
    }

    public function scopeWhereCancelled(Builder $query): void
    {
        $this->whereHas('payments', function (Builder $query) {
            $query->whereNotNull('cancelled_at');
        });
    }

    public function scopeWhereExpired(Builder $query): void
    {
        $this->whereHas('payments', function (Builder $query) {
            $query->whereNotNull('expired_at')->where('expired_at', '<', Date::now());
        });
    }
}
