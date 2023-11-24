<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Jobs\Activities\CheckActivityForFeatureMails;
use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Console\Output\OutputInterface;

class SendActivityFeatureMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        gumbo:send-activity-feature-mails
            {--sync : Force running in sync}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the notifications for each activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $command = $this->option('sync') ? 'dispatchSync' : 'dispatch';

        /** @var \Illuminate\Support\Collection<\App\Models\Activity> $activities */
        $activities = Activity::query()
            ->wherePublished()
            ->where('start_date', '>', Date::now())
            ->whereNull('cancelled_at')
            ->whereNull('postponed_at')
            ->get();

        $this->line(sprintf(
            'Found <info>%d</> activities that will be checked.',
            $activities->count(),
        ), null, OutputInterface::VERBOSITY_VERBOSE);

        foreach ($activities as $activity) {
            $this->line("Checking <info>{$activity->name}</>...", null, OutputInterface::VERBOSITY_VERBOSE);

            CheckActivityForFeatureMails::$command($activity);

            $this->line("Checked <info>{$activity->name}</>.");
        }
    }
}
