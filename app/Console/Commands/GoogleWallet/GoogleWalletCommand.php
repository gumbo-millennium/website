<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Services\Google\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

abstract class GoogleWalletCommand extends Command
{
    public function isHidden(): bool
    {
        return parent::isHidden() || ! App::make(WalletService::class)->isEnabled();
    }
}
