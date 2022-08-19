<?php

declare(strict_types=1);

namespace App\Services\Google;

use Illuminate\Support\Facades\Config;

final class WalletService
{
    use Traits\CreatesWalletIds;
    use Traits\CreatesWalletObjects;
    use Traits\MakesTicketClassApiCalls;
    use Traits\MakesTicketObjectApiCalls;
    use Traits\MakesWalletApiCalls;

    private bool $isEnabled;

    public function __construct()
    {
        $this->isEnabled = (bool) Config::get('services.google.wallet.enabled', false);
    }

    /**
     * Returns if the Google Wallet service is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
