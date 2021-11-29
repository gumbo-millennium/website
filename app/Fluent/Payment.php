<?php

declare(strict_types=1);

namespace App\Fluent;

use App\Models\Data\PaymentLine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use InvalidArgumentException;

/**
 * @property-read PaymentLine[] $lines
 * @property-read null|int $number
 * @property-read null|Model $model
 * @property-read null|User $user
 * @property-read null|string $description
 */
final class Payment extends Fluent
{
    public static function make(array $attributes = []): self
    {
        return new self($attributes);
    }

    public function __construct($attributes = [])
    {
        parent::__construct(array_merge([
            'lines' => [],
            'number' => null,
            'model' => null,
            'description' => null,
            'user' => null,
        ], $attributes));

        if ($this->attributes['model'] !== null && ! $this->attributes['model'] instanceof Model) {
            throw new InvalidArgumentException('Model must be null or an Eloquent model');
        }

        if (! is_array($this->attributes['lines'])) {
            throw new InvalidArgumentException('Payment lines must be an array');
        }

        if (! empty($this->attributes['lines']) && array_keys($this->attributes['lines']) !== range(0, count($this->attributes['lines']) - 1)) {
            throw new InvalidArgumentException('Payment lines must be non-associative');
        }

        foreach ($this->attributes['lines'] as $index => $line) {
            if ($line instanceof PaymentLine) {
                continue;
            }

            if (! is_array($line)) {
                throw new InvalidArgumentException("Payment line on index [${index}] must be an array");
            }

            $this->attributes['lines'][$index] = PaymentLine::fromArray($line);
        }
    }

    public function addLine(string $label, ?int $price, int $quantity = 1): self
    {
        $this->attributes['lines'][] = new PaymentLine($label, $price, $quantity);

        return $this;
    }

    public function withNumber(string $number): self
    {
        $this->attributes['number'] = $number;

        return $this;
    }

    public function withModel(Model $model): self
    {
        $this->attributes['model'] = $model;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->attributes['description'] = $description;

        return $this;
    }

    public function withUser(User $user): self
    {
        $this->attributes['user'] = $user;

        return $this;
    }

    public function getSum(): int
    {
        // Use higher-order sum
        // https://laravel.com/docs/6.x/collections#higher-order-messages
        return Collection::make($this->attributes['lines'])
            ->sum->getSum();
    }
}
