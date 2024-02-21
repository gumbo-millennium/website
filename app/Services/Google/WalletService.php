<?php

declare(strict_types=1);

namespace App\Services\Google;

use App\Services\Google\Traits\HandlesActivityTypes;
use App\Services\Google\Traits\HandlesEventClasses;
use App\Services\Google\Traits\HandlesEventObjects;
use App\Services\Google\Traits\HandlesJwtUrls;
use App\Services\Google\Traits\HandlesModels;
use Google\Service\Walletobjects as GoogleWalletService;
use Google\Service\Walletobjects\Resource\Eventticketclass as EventTicketClassResource;
use Google\Service\Walletobjects\Resource\Eventticketobject as EventTicketObjectResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

final class WalletService
{
    use HandlesActivityTypes;
    use HandlesEventClasses;
    use HandlesEventObjects;
    use HandlesJwtUrls;
    use HandlesModels;

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

    protected function getEventTicketClassApi(): EventTicketClassResource
    {
        return App::get(GoogleWalletService::class)->eventticketclass;
    }

    protected function getEventTicketObjectApi(): EventTicketObjectResource
    {
        return App::get(GoogleWalletService::class)->eventticketobject;
    }
}
