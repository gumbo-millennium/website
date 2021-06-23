<?php

declare(strict_types=1);

namespace App\Services\Payments;

use Illuminate\Support\Fluent;

/**
 * @method self type(string $type)
 * @method self name(string $name)
 * @method self quantity(int $quantity)
 * @method self unitPrice(Amount $unitPrice)
 * @method self discountAmount(?Amount $discountAmount)
 * @method self totalAmount(Amount $totalAmount)
 * @method self vatRate(string $vatRate)
 * @method self vatAmount(Amount $amount)
 * @method self sku(string $sku)
 * @method self imageUrl(string $imageUrl)
 * @method self productUrl(string $productUrl)
 * @method self metadata(array $metadata)
 */
class OrderLine extends Fluent
{
    public static function make(string $name, int $quantity, int $price, string $type = 'physical'): self
    {
        return new static([
            'type' => $type,

            'name' => $name,
            'quantity' => $quantity,

            'unitPrice' => Amount::fromInt($price),
            'totalAmount' => Amount::fromInt($price * $quantity),

            'vatRate' => '0.00',
            'vatAmount' => Amount::fromInt(0),
        ]);
    }
}
