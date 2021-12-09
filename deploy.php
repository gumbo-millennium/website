<?php

declare(strict_types=1);

namespace Deployer;

require 'recipe/laravel.php';

require '.deploy/hosts.php';
require '.deploy/tasks.php';

// Project name
set('application', 'gumbo-millennium');

// Project repository
set('repository', 'https://github.com/gumbo-millennium/website.git');

// Disable recursive git pull, since it's only for test data
set('git_recursive', false);

// Set the Nova path
set('nova_zip', '~/nova.zip');

// Shared files/dirs between deploys, merged with the Laravel recipe
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server, merged with the Laravel recipe
add('writable_dirs', []);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Re-map all tasks
desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'gumbo:replace-nova', // Replace the nova dummy with a live install
    'deploy:vendors',
    'gumbo:front-end', // Upload compiled front-end
    'deploy:writable',
    'artisan:storage:link',
    'gumbo:horizon:pause', // Pause the horizon supervisor
    'gumbo:migrate', // Run the database migrations
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:optimize',
    'artisan:event:cache', // Cache the events
    'gumbo:horizon:terminate', // Terminate all horizon supervisors
    'deploy:symlink',
    'gumbo:url', // Print the URL to the environment
    'deploy:unlock',
    'cleanup',
]);
