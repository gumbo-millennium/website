<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Paid;
use App\Models\User;
use Stripe\Charge;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidArgumentException;

trait HandlesStripeCharges
{
    /**
     * Charges retrieved from API
     * @var Charge[]
     */
    private array $chargeCache = [];

    /**
     * Returns the charge for this paid Enrollement
     * @param Enrollment $enrollment
     * @return null|Stripe\Charge
     */
    public function getCharge(Enrollment $enrollment): ?Charge
    {
        // Only available on paid invoice
        if (!$enrollment->state instanceof Paid) {
            return null;
        }

        // Check cached
        if (!empty($this->chargeCache[$enrollment->payment_invoice])) {
            return $this->chargeCache[$enrollment->payment_invoice];
        }

        // Get invoice
        $invoice = $this->getInvoice($enrollment);

        // Check for charge
        if (empty($invoice->charge)) {
            return null;
        }

        try {
            // Get charge
            $charge = Charge::retrieve($invoice->charge);

            // Cache charge
            $this->chargeCache[$invoice->id] = $charge;

            // Return charge
            return $charge;
        } catch (ApiErrorException $exception) {
            // Bubble any non-404 errors
            $this->handleError($exception, 404);

            // Log 404 (weird, but okay)
            logger()->info('Failed to find charge for {invoice}', compact('invoice'));
            return null;
        }
    }
}
