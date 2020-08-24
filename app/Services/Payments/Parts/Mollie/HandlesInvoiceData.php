<?php

declare(strict_types=1);

namespace App\Services\Payments\Parts\Mollie;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\PaymentMethods\DirectDebitMethod;
use App\Models\PaymentMethods\IdealMethod;
use App\Models\PaymentMethods\PaymentMethod;
use App\Models\PaymentMethods\TransferMethod;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

/**
 * Handles retrieval and parsing of Mollie payment methods
 */
trait HandlesInvoiceData
{
    /**
     * Returns the rate at which VAT is to be calculated
     * @return float percentage of price to count as VAT, should be between 0.00 - 1.00
     */
    protected static function getVatRate(): float
    {
        // Get rate from config
        return Config::get('gumbo.vat', 0.21);
    }

    /**
     * Get the tax value of an item, including if tax-free
     * @param int $price
     * @return array
     */
    protected static function makeVatPrice(int $price): array
    {
        // Get rate
        $vat = $this->getVatRate();

        // Convert to Price object
        return $this->makePrice($vat === 0 ? 0 : $price / (1 + $vat) * $vat);
    }

    /**
     * Converts the value to a price array
     * @param int|float|Activity|Enrollment $value
     * @return array
     * @throws InvalidArgumentException
     */
    protected static function makePrice($value): array
    {
        // Format ints
        if (is_int($value)) {
            return [
                'amount' => sprintf('%.2f', $value / 100),
                'currency' => 'EUR'
            ];
        }

        // Cast floats to int and recurse
        if (is_float($value)) {
            return $this->makePrice((int) floor($value * 100));
        }

        // Handle activity price
        if ($value instanceof Activity) {
            return $this->makePrice($value->total_price);
        }

        // Handle enrollment price
        if ($value instanceof Enrollment) {
            return $this->makePrice($value->total_price);
        }

        // Throw a fit
        throw new InvalidArgumentException("Unknown value specified for price conversion");
    }

    /**
     * Converts the price object to a cents representation
     * @param null|object $price
     * @return int
     */
    protected function readPrice(?object $price): int
    {
        return $price ? (int) (floatval($price->amount) * 100) : 0;
    }

    /**
     * Builds the invoice lines with discounts applied and a separate
     * invoice tax
     * @param Enrollment $enrollment
     * @return array
     */
    private function buildInvoiceProducts(Enrollment $enrollment): array
    {
        // Shorthands
        $activity = $enrollment->activity;
        $vatRate = $this->getVatRate();

        // Compute invoice line
        $productLine = [
            'name' => "Inschrijving {$activity->name}",
            'quantity' => 1,
            'unitPrice' => $this->makePrice($activity),
            'totalAmount' => $this->makePrice($enrollment),
            'vatRate' => sprintf('%.2f', $vatRate * 100),
            'vatAmount' => $this->makeVatPrice($enrollment->price),
            'productUrl' => URL::route('activity.show', compact('activity')),
        ];

        // Compute service fee line
        $serviceFee = $enrollment->total_price - $enrollment->price;
        $serviceFeeLine = [
            'type' => 'surcharge',
            'name' => "Transactiekosten",
            'quantity' => 1,
            'unitPrice' => $this->makePrice($serviceFee),
            'totalAmount' => $this->makePrice($serviceFee),
            'vatRate' => sprintf('%.2f', $vatRate * 100),
            'vatAmount' => $this->makeVatPrice($serviceFee)
        ];

        // Return as-is if there are no discounts
        if (!$enrollment->is_discounted) {
            return [$productLine, $serviceFeeLine];
        }

        // Compute discount line
        $discount = 0 - ($activity->price - $enrollment->price);

        // Also credit it somewhere
        $label = $activity->organiser ? "Ledenkorting vanuit {$$activity->organiser}" : "Ledenkorting";

        // Create item
        $discountLine = [
            'type' => 'discount',
            'name' => $label,
            'quantity' => 1,
            'unitPrice' => $this->makePrice($discount),
            'totalAmount' => $this->makePrice($discount),
            'vatRate' => sprintf('%.2f', $vatRate * 100),
            'vatAmount' => $this->makeVatPrice($discount)
        ];

        // Return info
        return [$productLine, $discountLine, $serviceFeeLine];
    }

    /**
     * Constructs invoice data
     * @param Enrollment $enrollment
     * @param PaymentMethod $method
     * @return array
     */
    private function buildInvoiceData(Enrollment $enrollment): array
    {
        // Shorthands
        $activity = $enrollment->activity;
        $user = $enrollment->user;

        // Add basic billing info
        $billingInfo = [
            'givenName' => $user->first_name,
            'familyName' => \ucfirst(trim("{$user->insert} {$user->last_name}")),
            'email' => $user->email,
            'phone' => $user->phone
        ];

        // Add address if present
        if (!empty($user->address) && !empty($user->address['line1'])) {
            $billingInfo = array_merge($billingInfo, [
                'streetAndNumber' => $user->address['line1'],
                'streetAdditional' => $user->address['line1'],
                'postalCode' => $user->address['postal_code'],
                'city' => $user->address['city'],
                'country' => Str::upper($user->address['country']),
            ]);
        }

        // Compute expiration date, which is at least one day
        $expire = $enrollment->expire;
        $minExpire = now()->addDay();
        if ($expire < $minExpire) {
            $expire = $minExpire;
        }

        return [
            'amount' => $this->makePrice($enrollment),
            'orderNumber' => $enrollment->id,
            'locale' => 'nl_NL',
            'lines' => $this->buildInvoiceProducts($enrollment),
            'webhookUrl' => URL::route('api.mollie.webhook'),
            'redirectUrl' => URL::route('enroll.pay-return', compact('activity')),
            'expiresAt' => $expire->format('Y-m-d'),
            'billingAddress' => $billingInfo
        ];
    }

    /**
     * Constructs the payment information for this invoice
     * @param Enrollment $enrollment
     * @param null|PaymentMethod $method
     * @return array
     */
    private function buildInvoicePayment(Enrollment $enrollment, ?PaymentMethod $method): array
    {
        // Make the initial mapping
        $mapping = [
            'description' => $enrollment->activity->full_statement,
            'locale' => 'nl_NL',
            'webhookUrl' => URL::route('api.mollie.webhook'),
            'redirectUrl' => URL::route('enroll.pay-return', ['activity' => $enrollment->activity]),
            'customerId' => $enrollment->user->getVendorId('mollie'),
            'metadata' => [
                'activity' => $enrollment->activity->name,
                'enrollment-id' => $enrollment->id,
                'user-id' => $enrollment->user->id,
            ]
        ];

        // Add method-specific config
        if ($method instanceof IdealMethod) {
            // Handle auto bank assignment for iDEAL
            $mapping['issuer'] = $method->getBank();
        } elseif ($method instanceof DirectDebitMethod) {
            // Handle auto-provision of name and IBAN
            $mapping['consumerName'] = $method->getName() ?? $enrollment->user->name;
            $mapping['consumerAccount'] = $method->getIban();
        } elseif ($method instanceof TransferMethod) {
            // Handle provisioning of email and settings
            $expire = $enrollment->expire;
            $minExpire = \now()->addDays(2);

            $mapping['billingEmail'] = $method->getEmail() ?? $enrollment->user->email;
            $mapping['locale'] = 'nl_NL';
            $mapping['dueDate'] = $expire > $minExpire ? $expire : $minExpire;
        }

        // Filter empty values
        $mapping = \array_filter($mapping);

        // Return Mollie data
        return [
            'method' => optional($method)->name,
            'payment' => $mapping
        ];
    }
}
