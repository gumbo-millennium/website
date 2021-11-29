<?php

declare(strict_types=1);

namespace Tests\Fixtures\Services;

use App\Contracts\Payments\Payable;
use App\Contracts\Payments\PaymentService;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Tests\TestCase as PHPUnit;

class DummyPaymentService implements PaymentService
{
    private array $data = [];

    public static function getName(): string
    {
        return 'dummy';
    }

    public function create(Payable $payable): Payment
    {
        $paymentData = $payable->toPayment();

        $payment = Payment::make([
            'provider' => self::getName(),
            'transaction_id' => Str::random(20),
            'price' => $paymentData->getSum(),
        ]);
        $payment->payable()->associate($payable);
        $payment->save();

        Arr::set($this->data, "{$this->objectKey($payable)}.payment", $payment);

        return $payment;
    }

    public function nextUrl(Payment $payment): ?string
    {
        return Arr::get($this->data, "{$this->objectKey($payment)}.next", null);
    }

    public function cancel(Payment $payment): void
    {
        Arr::set($this->data, "{$this->objectKey($payment)}.cancel", true);
    }

    public function isPaid(Payment $payment): bool
    {
        Arr::set($this->data, "{$this->objectKey($payment)}.checked-paid", true);

        return Arr::get($this->data, "{$this->objectKey($payment)}.paid", false);
    }

    public function isExpired(Payment $payment): bool
    {
        Arr::set($this->data, "{$this->objectKey($payment)}.checked-expired", true);

        return Arr::get($this->data, "{$this->objectKey($payment)}.expired", false);
    }

    public function isCancelled(Payment $payment): bool
    {
        Arr::set($this->data, "{$this->objectKey($payment)}.checked-cancelled", true);

        return Arr::get($this->data, "{$this->objectKey($payment)}.cancelled", false);
    }

    /**
     * @param Model|Payable|Payment $subject
     * @return void
     */
    public function setProperty($subject, string $key, bool $value): self
    {
        throw_unless(in_array($key, ['paid', 'expired', 'cancelled'], true), new LogicException('Invalid property key'));

        Arr::set($this->data, "{$this->objectKey($subject)}.${key}", $value);

        return $this;
    }

    public function assertWasSeen($subject): void
    {
        PHPUnit::assertArrayHasKey($this->objectKey($subject), $this->data, 'Failed asserting object was seen');
    }

    public function assertWasNotSeen($subject): void
    {
        PHPUnit::assertArrayNotHasKey($this->objectKey($subject), $this->data, 'Failed asserting object was not seen');
    }

    public function assertWasCreated(Payable $payable): void
    {
        $key = $this->objectKey($payable);

        PHPUnit::assertArrayHasKey($key, $this->data, 'Failed asserting the payable was seen');
        PHPUnit::assertArrayHasKey('created', $this->data[$key], 'Failed asserting a payment was created');
    }

    public function assertWasCancelled(Payment $payment): void
    {
        $key = $this->objectKey($payment);

        PHPUnit::assertArrayHasKey($key, $this->data, 'Failed asserting the payment was seen');
        PHPUnit::assertArrayHasKey('cancel', $this->data[$key], 'Failed asserting a payment was cancelled');
    }

    public function assertWasChecked(Payment $payment, string $check): void
    {
        throw_unless(in_array($check, ['paid', 'expired', 'cancelled'], true), new LogicException('Invalid check'));

        $key = $this->objectKey($payment);

        PHPUnit::assertArrayHasKey($key, $this->data, 'Failed asserting the payment was seen');
        PHPUnit::assertArrayHasKey("checked-${check}", $this->data[$key], "Failed asserting the payment was checked for [${check}]");
    }

    /**
     * @param Model|Payable $model
     */
    private function objectKey($model): string
    {
        if ($model instanceof Model) {
            return get_class($model) . ':' . $model->getKey();
        }
        if ($model instanceof Payable) {
            return get_class($model) . ':' . $model->toPayment()->number;
        }

        return get_class($model);
    }
}
