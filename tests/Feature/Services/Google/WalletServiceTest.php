<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Google;

use App\Services\Google\WalletService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    /**
     * @before
     */
    public function setupIssuerIdAndTraitBeforeTest(): void
    {
        $this->afterApplicationCreated(fn () => Config::set([
            'services.google.wallet.enabled' => true,
            'services.google.wallet.issuer_id' => '1001337',
        ]));
    }

    /**
     * Test core functionality of the WalletService.
     */
    public function test_initialisation(): void
    {
        /** @var WalletService $service */
        $service = App::make(WalletService::class);

        $this->assertInstanceOf(WalletService::class, $service);
    }
}
