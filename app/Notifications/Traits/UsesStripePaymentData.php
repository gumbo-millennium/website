<?php

declare(strict_types=1);

namespace App\Notifications\Traits;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use App\Services\IdealBankService;

trait UsesStripePaymentData
{
    private static array $defaultData = [
        'paid' => false,
        'refunded' => false,
        'bank' => null,
        'iban' => null,
    ];

    /**
     * Returns data for the enrollment
     * @param Enrollment $enrollment
     * @return array
     */
    protected function getPaymentInfo(Enrollment $enrollment): array
    {
        // Get payment services
        $stripeService = app(StripeServiceContract::class);
        $bankService = app(IdealBankService::class);

        // Sanity checks
        \assert($stripeService instanceof StripeServiceContract);
        \assert($bankService instanceof IdealBankService);

        // Get data
        $charge = $stripeService->getCharge($enrollment, null);

        // Fail if no charge was found
        if (!$charge) {
            return array_merge([], self::$defaultData);
        }

        // Get basic data
        $data = [
            'paid' => $charge->paid,
            'refunded' => $charge->refunded,
        ];

        // Get details of iDEAL payments
        $chargePaymentDetails = $charge->payment_method_details;
        if ($chargePaymentDetails && $chargePaymentDetails->type === 'ideal') {
            // Get bank
            $chargePaymentBank = object_get($chargePaymentDetails, 'ideal.bank');

            // Update data
            $data['bank'] = $bankService->getName($chargePaymentBank) ?? $chargePaymentBank;
            $data['bank'] = object_get($chargePaymentDetails, 'ideal.iban_last4');
        }

        // Check if paid
        return array_merge([], self::$defaultData, $data);
    }
}
