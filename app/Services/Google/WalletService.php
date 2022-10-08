<?php

declare(strict_types=1);

namespace App\Services\Google;

use App\Services\Google\Traits\HandlesActivityTypes;
use App\Services\Google\Traits\HandlesEventClasses;
use App\Services\Google\Traits\HandlesEventObjects;
use App\Services\Google\Traits\HandlesJwtUrls;
use App\Services\Google\Traits\HandlesModels;
use Google\Client as GoogleClient;
use Google_Service_Walletobjects as Walletobjects;
use Google_Service_Walletobjects_Eventticketclass_Resource as EventTicketClassResource;
use Google_Service_Walletobjects_Eventticketobject_Resource as EventTicketObjectResource;
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

    /**
     * Returns the Google Walletobjects API.
     * @return Walletobjects
     */
    protected function getGoogleWalletClient(): GoogleClient
    {
        return App::make('google_wallet_api');
    }

    protected function getEventTicketClassApi(): EventTicketClassResource
    {
        return App::make('google_wallet_eventticketclass_api');
    }

    protected function getEventTicketObjectApi(): EventTicketObjectResource
    {
        return App::make('google_wallet_eventticketobjects_api');
    }
}
