<?php

declare(strict_types=1);

namespace App\Services\Payments;

use DateTimeInterface;
use Illuminate\Support\Fluent;

/**
 * @method self billingAddress(Address $billingAddress)
 * @method self shippingAddress(Address $shippingAddress)
 * @method self redirectUrl(string $redirectUrl)
 * @method self webhookUrl(string $webhookUrl)
 * @method self locale(string $locale)
 * @method self method(string|string[] $method)
 * @method self vatRate(string $vatRate)
 * @method self vatAmount
 * @method self sku(string $sku)
 * @method self imageUrl(string $imageUrl)
 * @method self productUrl(string $productUrl)
 * @method self metadata(array $metadata)
 */
class Order extends Fluent
{
    private array $lines = [];

    public static function make(int $amount, string $orderNumber)
    {
        return new static([
            'amount' => Amount::fromInt($amount),
            'orderNumber' => $orderNumber,
        ]);
    }

    public function addLine(OrderLine $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiresAt): self
    {
        return $this['expiresAt'] = optional($expiresAt)->format('Y-m-d');
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'lines' => $this->lines,
        ]);
    }
}
