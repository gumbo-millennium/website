<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use App\Helpers\Str;
use App\Mail\Join\UserJoinMail;
use App\Mail\Shop\NewOrderBoardMail;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\JoinSubmission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Illuminate\Mail\Mailable;

/**
 * Makes purgecss mails.
 */
class MakePurgecssMails extends Command
{
    private const EMAIL_CLASSES = [
        UserJoinMail::class,
        NewOrderBoardMail::class,
        UserJoinMail::class,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purgecss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates e-mail templates for PurgeCSS to not ignore';

    private Container $container;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get models
        $models = $this->seedModels();

        $this->info('Models created');

        // Get path
        $filePath = \resource_path('assets/html/purgecss');

        try {
            foreach (self::EMAIL_CLASSES as $mailClass) {
                // Get name
                $mailName = \class_basename($mailClass);

                // Write
                $this->line("Creating <comment>{$mailName}</>...");

                // Make mail
                $mail = $this->container->make($mailClass, $models);
                \assert($mail instanceof Mailable);

                // Assign stuff
                $mail->to($models['user']);

                // Write
                $this->line("Rendering <comment>{$mailName}</>...");

                // Render mail
                $view = $mail->render();

                // Get file
                $fileName = sprintf('%s/mail-%s.html', $filePath, Str::snake(\class_basename($mail), '-'));

                // Write
                $this->line("Writing <comment>{$mailName}</> to <info>{$fileName}</>...");

                // Write file
                \file_put_contents($fileName, $view);

                // Done
                $this->info("Completed {$mailName}");
            }
        } finally {
            // Make sure to delete enrollment first
            $models['enrollment']->delete();

            // Now delete everything
            foreach ($models as $model) {
                if (! $model->exists) {
                    continue;
                }

                $model->delete();
            }

            $this->info('Models removed');
        }
    }

    /**
     * Returns a list of models.
     *
     * @return array<\Illuminate\Database\Eloquent\Model>
     * @throws BindingResolutionException
     */
    public function seedModels(): array
    {
        // Get activity
        $activity = Activity::factory()->create();

        // Get user
        $user = User::factory()->create();

        // Enroll user into activity
        $enrollment = Enrollment::factory()->for($activity)->for($user)->create();

        // Get a join submission
        $joinSubmission = JoinSubmission::factory()->create();

        return [
            'activity' => $activity,
            'user' => $user,
            'enrollment' => $enrollment,
            'joinSubmission' => $joinSubmission,
        ];
    }
}
