<?php

declare(strict_types=1);

namespace App\Contracts;

interface ConvertsToStripe
{
    /**
     * Returns Stripe-ready array
     *
     * @return array
     */
    public function toStripeCustomer(): array;
}
