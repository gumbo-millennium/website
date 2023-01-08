<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Activity;

use App\Jobs\SendActivityMessageJob;
use App\Models\ActivityMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Console\Output\OutputInterface;

class SendMessagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:activity:send-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends all pending messages that are yet to be sent.';

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
     *
     * @return int
     */
    public function handle()
    {
        $messagesToBeSend = ActivityMessage::query()
            // Only target unsent messages
            ->whereNull('sent_at')

            // Exclude messages for activities that ended >30 days ago
            ->whereHas('activity', function ($query) {
                $query->where('end_date', '>=', Date::now()->subDays(30)->endOfHour());
            })

            // Only sent messages that should have been sent or that have no scheduled date
            ->where(
                fn ($query) => $query
                    ->where('scheduled_at', '<=', Date::now())
                    ->orWhereNull('scheduled_at'),
            )

            // Ensure we get a cursor going, to avoid memory issues
            ->cursor();

        if ($messagesToBeSend->isEmpty()) {
            $this->comment('No messages to be sent');

            return Command::SUCCESS;
        }

        $dispatchCount = 0;

        foreach ($messagesToBeSend as $message) {
            $this->line(sprintf(
                'Sending message <info>%s</>: <comment>%s</> for activity <comment>%s</>',
                $message->id,
                $message->subject,
                $message->activity->name,
            ), null, OutputInterface::VERBOSITY_VERBOSE);

            SendActivityMessageJob::dispatchSync($message);

            $dispatchCount++;

            $this->info("Message {$message->id} sent", OutputInterface::VERBOSITY_VERBOSE);
        }

        $this->info("Successfully sent {$dispatchCount} scheduled messages");

        return Command::SUCCESS;
    }
}
