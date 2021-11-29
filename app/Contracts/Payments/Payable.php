<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

use App\Fluent\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Payable
{
    public function toPayment(): Payment;

    public function payments(): MorphMany;
}
