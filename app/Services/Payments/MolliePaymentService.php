<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Contracts\Payments\Payable;
use App\Contracts\Payments\PaymentService;
use App\Exceptions\PaymentException;
use App\Models\Data\PaymentLine;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrder;
use Mollie\Laravel\Facades\Mollie;
use Mollie\Laravel\Wrappers\MollieApiWrapper;

class MolliePaymentService implements PaymentService
{
    public static function getName(): string
    {
        return 'mollie';
    }

    public function create(Payable $payable): Payment
    {
        $payment = $payable->toPayment();

        if (! $payment->number || $payment->getSum() === 0 || ! $payment->model || ! $payment->user) {
            throw new PaymentException('Payment number, a non-zero pric, a model and a user are required');
        }

        $model = $payable->payments()->make([
            'provider' => self::getName(),
            'price' => $payment->getSum(),
        ]);

        /** @var User $user */
        $model->user()->associate($payment->user);

        $model->save();

        return $model;
    }

    public function nextUrl(Payment $payment): ?string
    {
        $molliePayment = $this->createOrGetMollieOrder($payment);

        if (! $molliePayment) {
            return null;
        }

        // Created status = pending payment
        if (! $molliePayment->isCreated()) {
            return null;
        }

        return $molliePayment->getCheckoutUrl();
    }

    public function cancel(Payment $payment): void
    {
        $molliePayment = $this->getMollieOrder($payment);

        if (! $molliePayment || ! $molliePayment->isCancelable) {
            return;
        }

        try {
            $this->getApi()->orders()->cancel($molliePayment->id);
        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                return;
            }

            throw new PaymentException($e->getMessage(), $e->getCode(), $e);
        }

        $payment->cancelled_at = Date::now();
        $payment->save(['cancelled_at']);
    }

    public function isPaid(Payment $payment): bool
    {
        $molliePayment = $this->getMollieOrder($payment);

        return $molliePayment && $molliePayment->isPaid();
    }

    public function isExpired(Payment $payment): bool
    {
        $molliePayment = $this->getMollieOrder($payment);

        return $molliePayment && $molliePayment->isExpired();
    }

    public function isCancelled(Payment $payment): bool
    {
        $molliePayment = $this->getMollieOrder($payment);

        return $molliePayment === null || $molliePayment->isCanceled();
    }

    private function getApi(): MollieApiWrapper
    {
        return Mollie::api();
    }

    private function createOrGetMollieOrder(Payment $payment): ?MollieOrder
    {
        if ($payment->transaction_id !== null) {
            return $this->getMollieOrder($payment);
        }

        $payable = $payment->payable;
        if (! $payable instanceof Payable) {
            return null;
        }

        $fluent = $payable->toPayment();
        $user = $payment->user;

        $userAddress = array_merge(Config::get('gumbo.fallbacks.address'), $user->address ?? []);

        $address = [
            'givenName' => $user->first_name,
            'familyName' => $user->last_name,
            'email' => $user->email,
            'streetAndNumber' => $userAddress['line1'],
            'postalCode' => $userAddress['postal_code'],
            'city' => $userAddress['city'],
            'country' => $userAddress['country'],
        ];

        $orderLines = [];

        /** @var PaymentLine $line */
        foreach ($fluent->lines as $line) {
            $orderLines[] = [
                'type' => $line->label === __('Fees') ? 'surcharge' : 'physical',
                'name' => $line->label,
                'quantity' => $line->quantity,
                'unitPrice' => [
                    'value' => sprintf('%.2f', $line->price / $line->quantity / 100),
                    'currency' => 'EUR',
                ],
                'totalAmount' => [
                    'value' => sprintf('%.2f', $line->price / 100),
                    'currency' => 'EUR',
                ],
                'vatRate' => '0.00',
                'vatAmount' => [
                    'value' => '0.00',
                    'currency' => 'EUR',
                ],
            ];
        }

        $orderModel = [
            'amount' => [
                'value' => sprintf('%.2f', $fluent->getSum() / 100),
                'currency' => 'EUR',
            ],
            'orderNumber' => $fluent->number,
            'lines' => $orderLines,
            'billingAddress' => $address,
            'redirectUrl' => route('payment.show', [$payment, 'verify' => true]),
            'webhookUrl' => route('api.webhooks.mollie'),
            'locale' => 'nl_NL',
            'method' => 'ideal',
            'metadata' => [
                'description' => $fluent->description,
            ],
        ];

        if (App::isLocal()) {
            unset($orderModel['webhookUrl']);
        }

        try {
            $molliePayment = $this->getApi()->orders()->create($orderModel);

            $payment->transaction_id = $molliePayment->id;
            $payment->save();

            return $molliePayment;
        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getMollieOrder(Payment $payment): ?MollieOrder
    {
        try {
            return $this->getApi()->orders()->get($payment->transaction_id);
        } catch (ApiException $exception) {
            if ($exception->getCode() === 404) {
                return null;
            }

            throw new PaymentException(
                "Failed to fetch Order from Mollie: {$exception->getMessage()}",
                $exception->getCode(),
                $exception,
            );
        }
    }
}
