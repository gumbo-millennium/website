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

// CHANGES
// Install Nova after updating the shared files
after('deploy:shared', 'gumbo:replace-nova');

// Install front-end after the back-end dependencies
after('deploy:vendors', 'gumbo:front-end');

// Migrate a little early, and disable Horizon beforehand
after('artisan:storage:link', 'gumbo:horizon:pause');
after('artisan:storage:link', 'gumbo:migrate');

// Cache the events after optimizing the application, and then kill Horizon
after('artisan:optimize', 'artisan:event:cache');
after('artisan:optimize', 'artisan:horizon:terminate');

// Print URL after symlinking
after('deploy:symlink', 'gumbo:url');

// Restart Horizon in case of deployment failure
after('deploy:failed', 'artisan:horizon:terminate');
