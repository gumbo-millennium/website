<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities\Traits;

use App\Models\Enrollment;

/**
 * Handles preparing data for Stripe
 */
trait FormatsStripeData
{
    /**
     * Returns common data, such as a price, currency and statement
     * description.
     * @param Enrollment $enrollment Enrollment to apply
     * @return array|null
     */
    public function getEnrollmentInformation(Enrollment $enrollment): ?array
    {
        // Return null if price is empty
        if (empty($enrollment->price)) {
            return null;
        }

        // Get shorthand user and activity
        $user = $enrollment->user;
        $activity = $enrollment->activity;

        // Assign description and statement
        $description = sprintf(
            'Inschrijving voor %s (%s)',
            $activity->name,
            $user->is_member ? 'lid' : 'bezoekers'
        );

        // Return data
        return [
            'currency' => 'eur',
            'amount' => $enrollment->total_price,
            'description' => $description,
            'receipt_email' => $user->email,
            'statement_descriptor' => $activity->full_statement,
            'metadata' => [
                'user-name' => $user->name,
                'user-id' => $user->id,
                'activity-id' => $activity->id,
                'enrollment-id' => $enrollment->id,
                'transfer-fee' => $enrollment->total_price - $enrollment->price
            ]
        ];
    }
}
