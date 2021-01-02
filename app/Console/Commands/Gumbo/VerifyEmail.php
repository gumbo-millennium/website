<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Console\Commands\Traits\FindsUserTrait;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Command
{
    use FindsUserTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:verify-email
                            {user : User to verify}
                            {--send : Send email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-sends the e-mail verification';

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
     * @return mixed
     */
    public function handle()
    {
        $user = $this->getUserArgument();

        if (!$user) {
            $this->error('Cannot find user');
            return false;
        }

        $sendUrl = $this->option('send');

        $this->line("Name:  <info>{$user->name}</>");
        $this->line("ID:    <comment>{$user->id}</>");
        $this->line("Email: <comment>{$user->email}</>");
        $this->line("Alias: <comment>{$user->alias}</>");
        $this->line("");
        if (!$this->confirm('Is this the correct user', false)) {
            $this->warn('User aborted');
            return false;
        }

        // Print URL
        $this->line('Verification URL:');
        $this->line($this->getSignUrl($user) . PHP_EOL);

        if (!$sendUrl) {
            return;
        }

        $user->sendEmailVerificationNotification();
        $this->info('Verification mail re-sent');
    }

    /**
     * Returns likely URL
     *
     * @param App\Models\User $user
     * @return string
     */
    private function getSignUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }
}
