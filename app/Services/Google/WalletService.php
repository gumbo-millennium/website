<?php

declare(strict_types=1);

namespace App\Services\Google;

final class WalletService
{
    use Traits\CreatesWalletIds;
    use Traits\CreatesWalletObjects;
    use Traits\MakesTicketClassApiCalls;
    use Traits\MakesTicketObjectApiCalls;
    use Traits\MakesWalletApiCalls;

    public function __construct()
    {
        $this->initializeCreatesWalletIds();
        $this->initializeMakesWalletApiCalls();
    }
}
