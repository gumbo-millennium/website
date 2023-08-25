<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Commands;

use Gumbo\ConscriboApi\Contracts\ConscriboApiClient;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conscribo:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    public function handle(ConscriboApiClient $client, CacheRepository $cache)
    {
        $this->verboseLine('Flushing Conscribo cache...');
        $cache->forget('conscribo.session_id');
        $cache->forget('conscribo.relations');
        $this->veryVerboseLine('Done', 'info');

        $this->line('Verifying login information', null, OutputInterface::VERBOSITY_VERBOSE);
        $session = $client->getSessionId();
        $this->veryVerboseLine("Done, session ID is <comment>{$session}</>.", 'info');
    }
}
