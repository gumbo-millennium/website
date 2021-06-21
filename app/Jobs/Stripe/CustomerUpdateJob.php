<?php

declare(strict_types=1);

namespace App\Jobs\Stripe;

use App\Contracts\StripeServiceContract;
use App\Models\User;

class CustomerUpdateJob extends StripeJob
{
    /**
     * User.
     */
    protected User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StripeServiceContract $service)
    {
        // Check for update
        $shouldUpdate = $this->user->stripe_id !== null;

        // Get customer stripe
        $customer = $service->getCustomer($this->user);

        // Get Skip if no update is required (the customer was created)
        if (! $shouldUpdate) {
            \logger()->info('Not issuing an update');

            return;
        }

        // Check for customer
        \logger()->info('Updating user {user} with id {stripe-id}', [
            'user' => $this->user,
            'stripe-id' => $customer->id,
        ]);

        // Update customer
        $customer->updateAttributes($this->user->toStripeCustomer());

        // Save changes
        $customer->save();
    }
}
