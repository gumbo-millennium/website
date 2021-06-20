<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Shop\Order;
use DateTimeInterface;
use LogicException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order as MollieOrder;

final class PaymentService
{
    private MollieApiClient $api;

    public function __construct(MollieApiClient $api)
    {
        $this->api = $api;
    }

    public function createForOrder(Order $order): MollieOrder
    {
        throw new LogicException('Not yet implemented.');
    }

    public function getRedirectUrl(Order $order): string
    {
        throw new LogicException('Not yet implemented.');
    }

    /**
     * Returns at what time the order was paid, if known.
     */
    public function paidAt(Order $order): DateTimeInterface
    {
        throw new LogicException('Not yet implemented.');
    }

    /**
     * Checks if an order is paid, does not mutate the $order.
     */
    public function isPaid(Order $order): bool
    {
        throw new LogicException('Not yet implemented.');
    }
}
