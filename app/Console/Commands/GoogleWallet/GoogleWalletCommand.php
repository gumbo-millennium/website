<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Services\Google\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GoogleWalletCommand extends Command
{
    public function isHidden(): bool
    {
        return parent::isHidden() || ! App::make(WalletService::class)->isEnabled();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! Config::get('services.google.wallet.enabled')) {
            $this->error('Google Wallet is not enabled');

            return 0;
        }

        return parent::execute($input, $output);
    }
}
