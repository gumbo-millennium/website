<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects;

class Money extends \Google\Model
{
    /**
     * @var string
     * @deprecated
     */
    public $kind;

    /**
     * @var string
     */
    public $micros;

    /**
     * @var string
     */
    public $currencyCode;

    /**
     * Creates the Money object for the price of the given cents.
     */
    public static function createForCents(?int $cents, string $currencyCode = 'EUR'): self
    {
        return new self([
            // To avoid having to compute large sums, use the cents and just add the missing zeros.
            'micros' => sprintf('%d', ($cents ?? 0) * 10000),
            'currencyCode' => $currencyCode,
        ]);
    }
}
