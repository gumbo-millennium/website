<?php

declare(strict_types=1);

namespace App\Services\Payments;

use Illuminate\Support\Fluent;

/**
 * @method self value(string $value)
 * @method self currency(string $currency)
 */
class Amount extends Fluent
{
    public static function fromInt(int $value, string $currency = 'EUR'): self
    {
        return new self([
            'value' => sprintf('%.2f', $value / 100),
            'currency' => $currency,
        ]);
    }
}
