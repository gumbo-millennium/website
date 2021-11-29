<?php

declare(strict_types=1);

namespace App\Events\Shop;

use App\Models\Shop\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @method static static dispatch(Order $order)
 */
abstract class OrderEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
