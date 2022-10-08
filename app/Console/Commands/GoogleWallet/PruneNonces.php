<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class PruneNonces extends GoogleWalletCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-wallet:prune-nonces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired nonces';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $files = Storage::allFiles('google-wallet/webhook/nonces');
        $expiration = Date::now()->sub(Config::get('gumbo.retention.wallet-nonces'))->timestamp;

        $deletion = [];

        foreach ($files as $file) {
            if (Storage::lastModified($file) < $expiration) {
                $deletion[] = $file;
            }
        }

        Storage::delete($deletion);

        $deletionCount = count($deletion);
        $this->line("Deleted <info>{$deletionCount}</info> expired Google Wallet nonce(s).");

        return Command::SUCCESS;
    }
}
