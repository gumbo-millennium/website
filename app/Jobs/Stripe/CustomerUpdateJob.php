<?php

namespace App\Jobs\Stripe;

use App\Models\User;
use Stripe\Customer;
use Stripe\Exception\UnknownApiErrorException;
use Stripe\Stripe;

class CustomerUpdateJob extends StripeJob
{
    /**
     * User
     *
     * @var User
     */
    protected $user;

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
    public function handle()
    {
        // Abort if Stripe key isn't set
        if (empty(Stripe::getApiKey())) {
            return;
        }

        // Update user if an ID is present and the update worked out okay
        if ($this->user->stripe_customer_id !== null && $this->update($this->user)) {
            return;
        }

        // Create a user otherwise
        $this->create($this->user);
    }

    /**
     * Update the existing user
     *
     * @param User $user
     * @return bool
     */
    public function update(User $user): bool
    {
        try {
            // Update user
            Customer::update($user->stripe_customer_id, [
                'name' => $user->name,
                'email' => $user->email
            ]);

            // Return true
            return true;
        } catch (UnknownApiErrorException $e) {
            // Return false on 404
            if ($e->getHttpStatus() === 404) {
                return false;
            }

            // Throw otherwise
            throw $e;
        }
    }

    /**
     * Creates a new customers
     *
     * @param User $user
     * @return void
     */
    public function create(User $user): void
    {
        // Create user
        $customer = Customer::create([
            'name' => $user->name,
            'email' => $user->email
        ]);

        // Assign user
        $user->stripe_customer_id = $customer->id;
        $user->save();
    }
}
