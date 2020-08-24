<?php

declare(strict_types=1);

namespace App\Services\Payments\Parts\Mollie;

use App\Helpers\Arr;
use App\Models\Activity;
use App\Models\PaymentMethods\DirectDebitMethod;
use App\Models\PaymentMethods\IdealMethod;
use App\Models\PaymentMethods\PaymentMethod;
use App\Models\PaymentMethods\TransferMethod;
use Illuminate\Support\Facades\Cache;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Issuer;
use Mollie\Api\Resources\Method;

/**
 * Handles retrieval and parsing of Mollie payment methods
 */
trait HandlesMethods
{
    /**
     * Returns a list of banks for the given activity
     * @param Activity $activity
     * @return array<PaymentMethod>
     */
    private function getBankListForActivity(Activity $activity): array
    {
        // Check cache first
        $cacheKey = static::CACHE_PREFIX . "payment-methods.{$activity->id}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Get API
        $client = $this->getMollieApi();

        // Get banks for this activity
        $apiMethods = $client->methods->allActive([
            'sequenceType' => 'first',
            'locale' => 'nl_NL',
            'include' => 'issuers,pricing',
            'amount' => [
                'currency' => 'EUR',
                'value' => sprintf('%.2f', $activity->price / 100)
            ]
        ]);

        // Walk items
        $methods = [];
        foreach ($apiMethods as $apiMethod) {
            // We should only be getting methods
            \assert($apiMethod instanceof Method);

            $methods[] = $this->apiMethodToPaymentMethd($apiMethod);
        }

        // Cache
        Cache::put($cacheKey, $methods, now()->addWeek());

        // Return
        return $methods;
    }

    /**
     * Converts a Mollie API method to a payment method
     * @param Method $method
     * @return PaymentMethod
     */
    public function apiMethodToPaymentMethd(Method $method): PaymentMethod
    {
        // Determine pricing
        $pricing = collect($method->pricing())->where('description', 'Netherlands')->first();
        $pricing ??= Arr::first($method->pricing());
        $pricing = [
            'fixed' => \floatval($pricing['fixed']['value'] ?? '0.00'),
            'variable' => \floatval($pricing['variable'] ?? '0.00'),
        ];

        // Prep default arguments
        $methodParams = [$method->id, $method->description, $pricing];
        $methodClass = PaymentMethod::class;

        if ($method->id === 'ideal') {
            // Handle iDeal banks
            $banks = [];
            foreach ($method->issuers as $issuer) {
                \assert($issuer instanceof Issuer);
                $banks[$issuer->id] = $issuer->name;
            }

            $methodClass = IdealMethod::class;
            $methodParams[] = $banks;
        } elseif ($method->id === 'sofort') {
            // Handle wiretransfer method
            $methodClass = TransferMethod::class;
        } elseif ($method->id === 'directdebit') {
            // Handle direct debit method
            $methodClass = DirectDebitMethod::class;
        }

        // Create
        return $methodClass::make($methodParams);
    }

    abstract protected function getMollieApi(): MollieApiClient;
}
