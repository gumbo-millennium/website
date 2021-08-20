<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Models\RedirectInstruction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class CleanupRedirectInstructions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:format-redirects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all redirects are properly formatted';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (RedirectInstruction::all() as $instruction) {
            if (empty($instruction->slug) || empty($instruction->path)) {
                continue;
            }

            if ($instruction->slug[0] === '/') {
                $instruction->slug = trim($instruction->slug, '/');
            }

            if (! URL::isValidUrl($instruction->path)) {
                $instruction->path = trim($instruction->path, '/');
            }

            $instruction->save();
        }
    }
}
