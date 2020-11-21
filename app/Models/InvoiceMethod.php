<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasMakeMethod;
use JsonSerializable;

/**
 * @method static InvoiceLine make(string $name, string $title, int $fixed, float $rate)
 */
class InvoiceMethod implements JsonSerializable
{
    use HasMakeMethod;

    public string $name;
    public string $title;
    public ?int $fixed;
    public ?float $rated;
    public array $options;

    /**
     * @param string $name Internal name
     * @param string $title Display name
     * @param int $fixed Fixed value
     * @param float $rated Value-based rate
     * @return void
     */
    public function __construct(string $name, string $title, ?int $fixed, ?float $rated)
    {
        $this->name = $name;
        $this->title = $title;
        $this->fixed = $fixed;
        $this->rated = $rated;
    }

    /**
     * Chainable assignment
     * @param array $options
     * @return InvoiceMethod
     */
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Computes the fee over the given value
     * @param int $value
     * @return int
     */
    public function computeFee(int $value): int
    {
        $fixedValue = $ratedValue = 0;
        if ($this->fixed) {
            $fixedValue = $this->fixed;
        }
        if ($this->rated) {
            $ratedValue = ceil($value / (100 + $this->rated) * $this->rated);
        }

        return $fixedValue + $ratedValue;
    }

    /**
     * Makes an invoice line for this method
     * @param int $total
     * @return InvoiceLine
     */
    public function toInvoiceLine(int $total): InvoiceLine
    {
        return InvoiceLine::make("Transactiekosten {$this->title}", $this->computeFee($total));
    }

    /**
     * @inheritdoc
     */
    public function JsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'fixed' => $this->fixed,
            'rated' => $this->rated,
            'options' => $this->options,
        ]
    }
}
