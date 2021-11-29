<?php

declare(strict_types=1);

namespace App\Events\Payments;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentExpired extends PaymentEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
}
