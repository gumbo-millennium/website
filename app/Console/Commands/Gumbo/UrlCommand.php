<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class UrlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:url {path=/ : Path to figure out}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prints the full URL to the application.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $path = $this->argument('path');

        $url = URL::to($path);

        $this->line(rtrim($url, '/'));

        return 0;
    }
}
