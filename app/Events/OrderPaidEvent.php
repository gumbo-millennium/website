<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Shop\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaidEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected Order $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
