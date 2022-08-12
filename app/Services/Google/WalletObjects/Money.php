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
     * @return Money
     */
    public static function createForCents(int $cents, string $currencyCode = 'EUR'): self
    {
        return new self([
            'micros' => $cents * 10000,
            'currencyCode' => $currencyCode,
        ]);
    }
}
