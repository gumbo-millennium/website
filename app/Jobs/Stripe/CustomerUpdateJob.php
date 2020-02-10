<?php

declare(strict_types=1);

namespace App\Jobs\Stripe;

use App\Contracts\StripeServiceContract;
use App\Models\User;

class CustomerUpdateJob extends StripeJob
{
    /**
     * User
     */
    protected User $user;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(StripeServiceContract $service)
    {
        // Check for update
        $shouldUpdate = $this->user->stripe_id !== null;

        // Get customer stripe
        $customer = $service->getCustomer($this->user);

        // Get Skip if no update is required (the customer was created)
        if (!$shouldUpdate) {
            return;
        }

        // Update customer
        $customer->updateAttributes($this->user->toStripeCustomer());

        // Save changes
        $customer->save();
    }
}
