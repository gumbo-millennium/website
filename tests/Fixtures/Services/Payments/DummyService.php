<?php

declare(strict_types=1);

namespace Tests\Fixtures\Services\Payments;

use App\Contracts\Payments\Payable;
use App\Contracts\Payments\PaymentService;
use App\Enums\PaymentStatus;
use App\Helpers\Str;
use App\Models\Payment;
use Exception;

class DummyService implements PaymentService
{
    private array $foundPayments = [];

    private array $paidPayments = [];

    private array $cancelledPayments = [];

    private array $expiredPayments = [];

    public static function getName(): string
    {
        return 'test-dummy';
    }

    public function create(Payable $payable): Payment
    {
        $payment = $payable->toPayment();

        $model = Payment::make([
            'price' => $payable,
            'provider' => self::getName(),
            'transaction_id' => Str::random(20),
        ]);

        if ($user = $payment->user) {
            $model->user()->associate($user);
        }

        $payable->payments()->save($model);

        return $model;
    }

    public function nextUrl(Payment $payment): ?string
    {
        if ($payment->status === PaymentStatus::OPEN) {
            return "https://example.com/pay/{$payment->transaction_id}";
        }

        return null;
    }

    public function cancel(Payment $payment): void
    {
        if (! array_key_exists($payment->id, $this->foundPayments)) {
            throw new Exception('Payment not found');
        }
    }

    public function isPaid(Payment $payment): bool
    {
        return array_key_exists($payment->id, $this->foundPayments)
            && array_key_exists($payment->id, $this->paidPayments);
    }

    public function isExpired(Payment $payment): bool
    {
        return array_key_exists($payment->id, $this->foundPayments)
            && array_key_exists($payment->id, $this->expiredPayments);
    }

    public function isCancelled(Payment $payment): bool
    {
        return array_key_exists($payment->id, $this->foundPayments)
            && array_key_exists($payment->id, $this->cancelledPayments);
    }

    public function markFound(Payment $payment): void
    {
        $this->foundPayments[$payment->id] = true;
    }

    public function markPaid(Payment $payment): void
    {
        $this->paidPayments[$payment->id] = true;
    }

    public function markCancelled(Payment $payment): void
    {
        $this->cancelledPayments[$payment->id] = true;
    }

    public function markExpired(Payment $payment): void
    {
        $this->expiredPayments[$payment->id] = true;
    }
}
