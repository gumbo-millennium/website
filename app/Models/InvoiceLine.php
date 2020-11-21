<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasMakeMethod;
use JsonSerializable;

/**
 * @method static InvoiceLine make(string $description, int $price, int $amount = 1)
 */
class InvoiceLine implements JsonSerializable
{
    use HasMakeMethod;

    public string $description = 'Unnamed product';
    public int $amount = 1;
    public int $price = 0;

    public function __construct(string $description, int $price, int $amount = 1)
    {
        $this->description = $description;
        $this->price = $price;
        $this->amount = $amount;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            'description' => $this->description,
            'price' => $this->price,
            'amount' => $this->amount,
        ];
    }
}
