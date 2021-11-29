<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

use App\Models\Payment;

interface PaymentService
{
    public static function getName(): string;

    public function create(Payable $payable): Payment;

    public function nextUrl(Payment $payment): ?string;

    public function cancel(Payment $payment): void;

    public function isPaid(Payment $payment): bool;

    public function isExpired(Payment $payment): bool;

    public function isCancelled(Payment $payment): bool;
}
