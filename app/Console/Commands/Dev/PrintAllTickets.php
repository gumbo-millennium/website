<?php

declare(strict_types=1);

namespace App\Console\Commands\Dev;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Ticket;
use Illuminate\Console\Command;

class PrintAllTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:print-tickets {--E|enrollments : Include enrollments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print activities with their tickets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (Activity::with('tickets')->cursor() as $activity) {
            $this->line(sprintf('<info>% 3d</>: <comment>%s</>', $activity->id, $activity->name));

            /** @var Ticket $ticket */
            foreach ($activity->tickets as $ticket) {
                $this->line(sprintf(
                    '  <info>%s</>: (%s, %s / %s, %s)',
                    $ticket->title,
                    Str::price($ticket->price) ?? 'gratis',
                    $ticket->quantity_available ?? '∞',
                    $ticket->quantity ?? '∞',
                    $ticket->members_only ? 'members' : 'public',
                ));

                if ($this->option('enrollments')) {
                    foreach ($activity->enrollments as $enrollment) {
                        $this->line(sprintf(
                            '    <info>%s</> (<comment>%s</>)',
                            $enrollment->user->name,
                            Str::price($enrollment->price) ?? 'gratis',
                        ));
                    }
                }
            }
        }
    }
}
