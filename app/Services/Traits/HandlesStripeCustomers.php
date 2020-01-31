<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Models\User;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

trait HandlesStripeCustomers
{
    /**
     * Returns the customer for this user
     * @param User $user
     * @return Stripe\Customer
     */
    public function getCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            try {
                // Return customer
                return Customer::retrieve($user->stripe_customer_id);
            } catch (ApiErrorException $exception) {
                // Bubble any non-404 errors
                $this->handleError($exception, 404);
            }
        }

        try {
            // Create customer
            $customer = Customer::create($user->toStripeCustomer());

            // Update user
            $user->stripe_customer_id = $customer->id;
            $user->save(['stripe_customer_id']);

            // Return customer
            return $customer;
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }
}
