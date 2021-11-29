<?php

declare(strict_types=1);

namespace App\Models\Data;

use App\Helpers\Arr;
use InvalidArgumentException;
use JsonSerializable;

class PaymentLine implements JsonSerializable
{
    public string $label;

    public ?int $price;

    public int $quantity;

    public static function fromArray(array $paymentLine): self
    {
        if (! Arr::has($paymentLine, ['label', 'price'])) {
            throw new InvalidArgumentException('PaymentLine must have label and price');
        }

        if (! is_int($paymentLine['price']) || $paymentLine['price'] < 0) {
            throw new InvalidArgumentException('PaymentLine must have a non-negative price as an integer');
        }

        $quantity = $paymentLine['quantity'] ?? 1;
        if (! is_int($quantity) || $quantity <= 0) {
            throw new InvalidArgumentException('PaymentLine must have a positive quantity as an integer');
        }

        return new self($paymentLine['label'], $paymentLine['price'], $quantity);
    }

    public static function make(string $label, ?int $price, int $quantity = 1): self
    {
        return new self($label, $price, $quantity);
    }

    public function __construct(string $label, ?int $price, int $quantity = 1)
    {
        $this->label = $label;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getSum(): ?int
    {
        if ($this->price === null) {
            return null;
        }

        return $this->price * max(1, $this->quantity);
    }

    public function jsonSerialize(): array
    {
        return [
            'label' => $this->label,
            'price' => $this->price,
            'quantity' => $this->quantity,
        ];
    }
}
