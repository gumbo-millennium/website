<?php

declare(strict_types=1);

namespace App\Services\Payments\Parts\Mollie;

use App\Models\User;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;

/**
 * Handles retrieval and parsing of Mollie payment methods
 */
trait HandlesCustomerUpdates
{
    /**
     * Performs a small update on a user
     */
    private function updateUser(User $user): void
    {
        // Get an API
        $api = $this->getMollieApi();

        // Check if exists
        $mollieId = $user->getVendorId('mollie');
        if ($mollieId) {
            try {
                // Get user
                $mollieCustomer = $api->customers->get($mollieId);

                // See if we have to update
                if (
                    $mollieCustomer->name === $user->name &&
                    $mollieCustomer->email === $user->email
                ) {
                    // No update required
                    return;
                }

                // Update fields
                $mollieCustomer->name = $user->name;
                $mollieCustomer->email = $user->email;

                // Send request
                $mollieCustomer->update();

                // Done
                return;
            } catch (ApiException $exception) {
                // report and continue, we'll create a new user
                \report($exception);

                // Not a "user not found" error, so don't do anything
                if ($exception->getCode() !== 404) {
                    return;
                }
            }
        }

        // Make the new user
        $apiUser = $api->customers->create([
            'name' => $user->name,
            'email' => $user->email,
            'locale' => 'nl_NL'
        ]);

        // Set vendor ID
        $user->setVendorId('mollie', $apiUser->id);

        // Save without triggering another update
        $user->withoutEvents(static fn () => $user->save());
    }

    abstract protected function getMollieApi(): MollieApiClient;
}
