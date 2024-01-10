<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Activity;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class SendSystemMessagesCommand extends Command
{
    private const LOG_FORMAT = 'd-m-Y H:i:s';

    private const CONFIG_FILE_RESOURCE_PATH = 'assets/yaml/scheduled-mails.yaml';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        gumbo:activity:send-system-messages
            {--send : Actually send the messages, instead of only displaying}
            {--validate : Simply validate the Yaml}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends system messages to users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Load file, assume it's valid since the pipeline will check it
        $file = Yaml::parseFile(resource_path(self::CONFIG_FILE_RESOURCE_PATH));

        // Check every row
        Collection::make($file['activities'])->each(fn ($message) => $this->checkAndSendMessage($message));

        // Done
        $this->info($this->option('validate') ? 'System configuration is valid.' : 'Done');

        return self::SUCCESS;
    }

    private function checkAndSendMessage(array $message): void
    {
        // Load options
        $isValidaton = $this->option('validate');
        $isDryRun = ! $this->option('send');

        // Load params
        $name = $message['name'];
        $offset = $message['offset'];
        $template = $message['template'];
        $recipient = $message['recipient'];
        $requirements = $message['requirements'] ?? [];

        // Load requirements
        $needsTickets = in_array('tickets', $requirements, true);

        // Validate time if requested
        if ($isValidaton) {
            try {
                $diff = -Date::now()->add($offset)->diffInDays(null, false);

                if ($diff > 99) {
                    throw new RuntimeException("Date offset {$offset} is too many days away from now.");
                }

                if ($diff < 0) {
                    throw new RuntimeException("Date offset {$offset} is beyond the start of the event, and that's not supported.");
                }
            } catch (Throwable $e) {
                $this->error("Failed to parse duration [<fg=red>{$offset}</>] for <info>{$name}</>");

                throw new RuntimeException("Offset for entry is invalid: {$e->getMessage()}", 0, $e);
            }

            if (! in_array($recipient, ['host', 'guests'], true)) {
                $this->error("Invalid recipient [<fg=red>{$recipient}</>] for <info>{$name}</>");

                throw new RuntimeException("Invalid recipient [{$recipient}] specified");
            }
        }

        // This system should run once every hour
        $startDate = Date::now()->startOfHour()->add($offset);
        $endDate = (clone $startDate)->addHour()->subSecond();

        $this->line("Checking {$name}...", null, OutputInterface::VERBOSITY_VERBOSE);
        $this->line(sprintf('Should be between <info>%s</> - <info>%s</>', $startDate->format(self::LOG_FORMAT), $endDate->format(self::LOG_FORMAT)), null, OutputInterface::VERBOSITY_DEBUG);

        // Validate template
        if (! class_exists($template) || ! is_a($template, Mailable::class, true)) {
            $this->error("Template <info>{$template}</info> does not exist or is not a Mailable");

            throw new RuntimeException("Invalid template [{$template}] specified");

            return;
        }

        // Done if running validation
        if ($this->option('validate')) {
            return;
        }

        // Find activities
        $applicable = Activity::query()
            ->whereBetween('start_date', [$startDate, $endDate])
            ->when($needsTickets, fn ($query) => $query->has('tickets'))
            ->with((['enrollments' => fn ($query) => $query->with('ticket', 'user')->stable()]))
            ->cursor();

        $this->line("Found {$applicable->count()} activities", null, OutputInterface::VERBOSITY_VERBOSE);

        // Check if there are any activities
        if ($applicable->isEmpty()) {
            $this->line("No activities found, skipping <info>{$name}</info>");

            return;
        }

        Log::info('Found {applicable_count} activity/activities to send {name} to. Target recipient is {recipient}', [
            'applicable_count' => $applicable->count(),
            'name' => $name,
            'recipient' => $recipient,
        ]);

        // Send messages
        foreach ($applicable as $activity) {
            /** @var Collection $recipients */
            $recipients = match($recipient) {
                'host' => User::whereIn('id', [
                    $activity->created_by_id,
                    $activity->updated_by_id,
                ])->get(),
                // Weird ass chain, but otherwise we get enrollments that aren't stable.
                'guests' => $activity->enrollments()->stable()->with('user')->get()->pluck('user')->unique(),
                default => throw new RuntimeException('Invalid recipient specified'),
            };

            // Skip if no recipients
            if ($recipients->isEmpty()) {
                $this->line("No recipients found for <info>{$activity->name}</info>, skipping");

                continue;
            }

            // Map enrollments
            $enrollments = $activity->enrollments->keyBy('user_id');

            // Send message to each individual recipient
            foreach ($recipients as $recipient) {
                $this->line("Sending message to <info>{$recipient->name}</info> for <info>{$activity->name}</info>");

                // Send message
                $enrollment = $enrollments->get($recipient->id);
                $mailable = App::make($template, [
                    'activity' => $activity,
                    'enrollment' => $enrollment,
                    'ticket' => $enrollment?->ticket,
                    'user' => $recipient,
                ]);

                if ($isDryRun) {
                    $this->line("Would send message <fg=cyan>{$name}</> to <fg=green>{$recipient->name} ({$recipient->id})</> for <fg=yellow>{$activity->name}</>");
                    $this->line("Message would be sent to <fg=green>{$recipient->email}</>", null, OutputInterface::VERBOSITY_VERBOSE);

                    continue;
                }

                Log::info('Sending message {name} to {recipient_name} ({recipient_id}) for {activity}', [
                    'name' => $name,
                    'recipient_name' => $recipient->name,
                    'recipient_id' => $recipient->id,
                    'activity' => $activity->name,
                ]);

                $this->line("Sending message <fg=cyan>{$name}</> to <fg=green>{$recipient->name} ({$recipient->id})</> for <fg=yellow>{$activity->name}</>");
                Mail::to($recipient)->queue($mailable);
            }
        }
    }
}
