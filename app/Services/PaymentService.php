<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Arr;
use App\Helpers\Str;
use App\Models\Shop\Order;
use DateTimeInterface;
use Doctrine\Common\Cache\Psr6\InvalidArgument;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order as MollieOrder;
use Mollie\Api\Resources\Payment;
use RuntimeException;
use UnexpectedValueException;

final class PaymentService
{
    private MollieApiClient $api;

    private array $cache = [];

    public function __construct(MollieApiClient $api)
    {
        $this->api = $api;
    }

    public function createForOrder(Order $order): MollieOrder
    {
        $user = $order->user;

        $safeAddress = Config::get('gumbo.fallbacks.address');
        $address = [];

        foreach ($safeAddress as $field => $value) {
            $address[$field] == Arr::get($user->address, $field, $value);
        }

        $orderLines = [];
        foreach ($order->variants as $variant) {
            $orderLines[] = [
                'type' => 'physical',
                'category' => 'gift',
                'name' => $variant->display_name,
                'quantity' => $variant->pivot->amount,
                'unitPrice' => $this->currency($variant->pivot->price),
                'totalAmount' => $this->currency($variant->pivot->price * $variant->pivot->amount),
                'vatRate' => '0.00 %',
                'vatAmount' => $this->currency(0),
                'sku' => $variant->sku,
                'imageUrl' => $variant->valid_image_url,
                'productUrl' => $variant->url,
            ];
        }

        $transferFee = $this->currency(Config::get('gumbo.fees.shop-order'));
        $orderLines[] = [
            'type' => 'surcharge',
            'category' => 'gift',
            'name' => 'Transactiekosten',
            'quantity' => 1,
            'unitPrice' => $transferFee,
            'totalAmount' => $transferFee,
            'vatRate' => '0.00 %',
            'vatAmount' => $this->currency(0),
        ];

        return $this->api->orders->create([
            'amount' => $this->currency($order->price),
            'orderNumber' => $order->number,
            'lines' => $orderLines,
            'billingAddress' => [
                // Name
                'givenName' => $user->first_name,
                'familyName' => trim("{$user->insert} {$user->last_name}"),

                // Contact details
                'email' => $user->email,
                'phone' => $user->phone,

                // Address
                'streetAndNumber' => $user->address['line1'],
                'streetAdditional' => $user->address['line2'],
                'postalCode' => $user->address['postal_code'],
                'city' => $user->address['city'],
                'country' => Str::upper($user->address['country']),

                // Redirect URL
                'redirectUrl' => route('shop.order.return', $order),
                'webhookUrl' => route('api.webhooks.shop'),

                // Method and expiration settings
                'method' => 'ideal',

                // Expiration date (always +24 hours)
                'expiresAt' => $order->expires_at->format('Y-m-d'),
            ],
            'embed' => [
                'payments',
            ],
        ]);
    }

    public function getRedirectUrl(Order $order): string
    {
        // Paid orders don't have a redirect URL
        if ($order->paid_at !== null) {
            return null;
        }

        try {
            $mollieOrder = $this->findOrder($order);

            // Paid upstream but webhook not yet processed.
            if ($mollieOrder->isPaid()) {
                return null;
            }

            // Check for a checkout URL, might be null in case
            // the payment was cancelled or has failed.
            $existingUrl = $mollieOrder->getCheckoutUrl();
            if ($existingUrl) {
                return $existingUrl;
            }

            // Create a new payment and return that URL
            /** @var Payment $payment */
            $payment = $mollieOrder->createPayment([]);

            return $payment->getCheckoutUrl();
        } catch (InvalidArgument $exception) {
            return null;
        }
    }

    /**
     * Returns at what time the order was paid, if known.
     */
    public function paidAt(Order $order): DateTimeInterface
    {
        try {
            $mollieOrder = $this->findOrder($order);

            if (! $mollieOrder->isPaid()) {
                return null;
            }

            return Date::parse($mollieOrder->paidAt)->toImmutable();
        } catch (InvalidArgument $exception) {
            return null;
        }
    }

    /**
     * Checks if an order is paid, does not mutate the $order.
     */
    public function isPaid(Order $order): bool
    {
        if ($order->paid_at !== null) {
            return true;
        }

        return $this->paidAt($order) !== null;
    }

    private function currency(?int $value): ?array
    {
        return $value === null ? null : [
            'currency' => 'EUR',
            'value' => sprintf('%.2f', $value / 100),
        ];
    }

    private function findOrder(Order $order): MollieOrder
    {
        if (! $order->payment_id) {
            throw new UnexpectedValueException('No Mollie order for this Gumbo order yet', 404);
        }

        try {
            return $this->order[$order->payment_id] ??= $this->api->orders->get($order->payment_id, [
                'embed' => [
                    'payments',
                    'refunds',
                ],
            ]);
        } catch (ApiException $apiException) {
            throw new RuntimeException(
                "API call to Mollie failed: {$apiException->getMessage()}",
                $apiException->getCode(),
                $apiException
            );
        }

        return $this->order[$order->payment_id];
    }
}
