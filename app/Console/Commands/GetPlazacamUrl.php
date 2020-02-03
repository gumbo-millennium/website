<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class GetPlazacamUrl extends Command
{
    private const CAMERA_NAMES = ['plaza', 'coffee'];
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'plazacam:url {--U|user= : User ID or email}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Gets a URL for the different webcams';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        // Get the user
        $userId = $this->option('user');
        $userQuery = User::query()->permission(['plazacam-update']);

        // Add user idenifier if present
        if ($userId) {
            $userQuery = $userQuery->where(static fn ($query) => $query->where('id', $userId)
                    ->orWhere('email', $userId));
        }

        // Get user
        $user = $userQuery->first();

        if (!$user) {
            $this->error('Cannot find an eligible user to generate URLs');
            return false;
        }

        // Print
        $this->line("Making URL for <info>{$user->name}</>.\n");

        // Update
        $this->line('Update cam');
        foreach (self::CAMERA_NAMES as $cam) {
            $this->line(sprintf('<info>%s</>: (PUT) <comment>%s</>', $cam, URL::signedRoute('api.plazacam.store', [
                'image' => $cam,
                'user' => $user
            ])));
        }

        // View
        $this->line("\nView cam");
        foreach (self::CAMERA_NAMES as $cam) {
            $this->line(sprintf('<info>%s</>: (GET) <comment>%s</>', $cam, URL::signedRoute('api.plazacam.view', [
                'image' => $cam,
                'user' => $user
            ])));
        }
    }
}
