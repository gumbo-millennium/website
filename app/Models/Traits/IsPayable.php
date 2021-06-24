<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Contracts\Payments\PayableModel;
use App\Helpers\Arr;
use App\Models\User;
use App\Services\Payments\Address;
use App\Services\Payments\Order;
use Illuminate\Support\Facades\Config;

trait IsPayable
{
    /**
     * Returns a Mollie order from this model.
     */
    abstract public function toMollieOrder(): Order;

    /**
     * Returns the field to store the payment ID in.
     */
    public function getPaymentIdField(): string
    {
        return 'payment_id';
    }

    /**
     * Returns the field to store when the object was paid.
     */
    public function getPaidAtField(): string
    {
        return 'paid_at';
    }

    /**
     * Returns the field to store when the object was cancelled.
     */
    public function getCancelledAtField(): string
    {
        return 'cancelled_at';
    }

    /**
     * Returns the field to store when the object was shipped.
     */
    public function getCompletedAtField(): string
    {
        return 'completed_at';
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->{$this->getCancelledAtField()} !== null) {
            return PayableModel::STATUS_CANCELLED;
        }

        if ($this->{$this->getPaymentIdField()} === null) {
            return PayableModel::STATUS_UNKNOWN;
        }

        if ($this->{$this->getCompletedAtField()} !== null) {
            return PayableModel::STATUS_COMPLETED;
        }

        if ($this->{$this->getPaidAtField()} !== null) {
            return PayableModel::STATUS_PAID;
        }

        return PayableModel::STATUS_OPEN;
    }

    /**
     * Returns a valid address to ship to.
     */
    protected function getPaymentAddressForUser(User $user): Address
    {
        $userAddress = $user->address;
        if (! Arr::has($userAddress ?? [], ['line1', 'postal_code', 'city', 'country'])) {
            $userAddress = Config::get('gumbo.fallbacks.address');
        }

        return Address::make()
            ->givenName($user->first_name)
            ->familyName(trim("{$user->insert} {$user->last_name}"))

            ->email($user->email)
            ->phone($user->phone)

            ->streetAndNumber(Arr::get($userAddress, 'line1'))
            ->streetAdditional(Arr::get($userAddress, 'line2'))
            ->city(Arr::get($userAddress, 'city'))
            ->postalCode(Arr::get($userAddress, 'postal_code'))
            ->country(Arr::get($userAddress, 'country'));
    }
}
