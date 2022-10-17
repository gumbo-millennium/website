<?php

declare(strict_types=1);

use function Deployer\desc;
use function Deployer\run;
use function Deployer\task;
use function Deployer\upload;
use function Deployer\writeln;

desc('Execute artisan horizon:pause');
task('gumbo:horizon:pause', function () {
    run('{{bin/php}} {{release_path}}/artisan horizon:pause');
});

desc('Execute artisan horizon:terminate');
task('gumbo:horizon:terminate', function () {
    run('{{bin/php}} {{release_path}}/artisan horizon:terminate || true');
});

desc('Execute artisan down');
task('gumbo:down', function () {
    run('{{bin/php}} {{release_path}}/artisan down --render="errors::503" || true');
});

desc('Execute artisan up');
task('gumbo:up', function () {
    run('{{bin/php}} {{release_path}}/artisan up || true');
});

desc('Prints the URL of the environment, for debug purposes.');
task('gumbo:url', function () {
    $appUrl = run('php {{release_path}}/artisan gumbo:url');
    writeln("Application live on <<info>{$appUrl}</>>.");
    writeln("::set-output name=url::{$appUrl}");
});

desc('Uploads the front-end');
task('gumbo:upload-frontend', function () {
    upload('public/', '{{release_path}}/public');
});

desc('Links the Font Awesome icons from the shared root folder');
task('gumbo:link-icons', function () {
    run('{{release_path}}/resources/bin/install-font-awesome deployment');
});

desc('Helper to run all front-end commands');
task('gumbo:front-end', [
    'gumbo:upload-frontend',
    'gumbo:link-icons',
]);

desc('Helper to run all migration commands');
task('gumbo:migrate', [
    'artisan:migrate:status',
    'artisan:migrate',
    'artisan:db:seed',
]);
