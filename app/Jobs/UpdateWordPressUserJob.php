<?php

namespace App\Jobs;

use App\Models\CorcelUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateWordPressUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The default role for a user.
     *
     * @var array
     */
    const WP_DEFAULT_ROLE = ['subscriber', 0];

    /**
     * maps permissions to roles and user levels.
     * LEVELS AND ROLES MUST BE ASCENDING!
     *
     * @var array
     */
    const WP_ROLE_MAP = [
        'content' => ['contributor', 1],
        'content-publish' => ['author', 2],
        'content-all' => ['editor', 7],
        'content-admin' => ['administrator', 8]
    ];

    /**
     * User object
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get user
        $user = $this->user;

        // Find WordPress user
        $wpUser = $user->wordpress;

        if (!$wpUser) {
            // Create a new WordPress object for the user
            $wpUser = CorcelUser::firstOrCreate([
                'user_login' => $user->email
            ]);

            // Save Wordpress user on user
            $user->wordpress_username = $wpUser->user_login;
            $user->wordpress()->save($wpUser);
            $user->save(['wordpress_username']);
        }

        // Update fields
        $wpUser->fill([
            'user_pass' => $user->password,
            'user_nicename' => str_slug($user->name),
            'user_email' => $user->email,
            'display_name' => $user->name
        ]);

        // Save WordPress changes
        $wpUser->save();

        // Get correct role
        list($topRole, $topLevel) = self::WP_DEFAULT_ROLE;

        // Loop through permissions, but only if not trashed
        if (!$user->trashed()) {
            foreach (self::WP_ROLE_MAP as $permission => $roleLevel) {
                if ($user->hasPermissionTo($permission)) {
                    list($topRole, $topLevel) = $roleLevel;
                }
            }
        }

        // Update rank of the user
        $wpUser->saveMeta([
            'wp_capabilities' => serialize([
                $topRole => true
            ]),
            'wp_user_level' => $topLevel
        ]);
    }
}
