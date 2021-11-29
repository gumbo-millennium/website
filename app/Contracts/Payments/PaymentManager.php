<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

interface PaymentManager
{
    public function find(string $service): ?PaymentService;

    public function default(): PaymentService;

    public function getDefault(): string;
}
