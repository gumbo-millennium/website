<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

/**
 * Auto download an ignore file for this project.
 * @author Roelof Roos
 * @license MPL-2.0
 */
class MakeGitignore extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:gitignore';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Updates the .gitignore file';

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
        // Find app root dir
        $rootDir = realpath(substr(__DIR__, 0, strrpos('app' . DIRECTORY_SEPARATOR, __DIR__)));
        if (!$rootDir) {
            $this->error(sprintf(
                'Failed to determine root path form __DIR__: %s',
                __DIR__
            ));
        }

        $this->line(sprintf(
            "Determined directory as <info>%s</info>",
            $rootDir
        ));

        // Download ignore file from https://gitignore.io
        $downloadUrl = 'https://www.gitignore.io/api/laravel,node';

        $this->line(sprintf(
            "Downloading ignore file from <info>%s</info>...",
            $downloadUrl
        ));

        $client = new Client();
        $data = $client->request('GET', $downloadUrl);

        if ($data->getStatusCode() !== 200) {
            $this->alert(sprintf(
                "Failed to download file; got %d %s",
                $data->getStatusCode(),
                $data->getReasonPhrase()
            ));
            return false;
        }

        $ignoreContent = $data->getBody() . <<<'IGNORE'

### App config ###

# Public files and directories
/public/
!/public/favicon.ico
!/public/index.php
!/public/robots.txt

# Development helper
storage/debugbar

# Laravel IDE helper
/_ide_helper_models.php
/_ide_helper.php
/.phpstorm.meta.php
IGNORE;
        // Trim lines and add trailing EOL
        $ignoreContent = trim($ignoreContent) . PHP_EOL;

        $this->line("Writing contents...");
        $ignoreFile = "$rootDir/.gitignore";

        file_put_contents($ignoreFile, $ignoreContent);

        if (md5_file($ignoreFile) === md5($ignoreContent)) {
            $this->info('Updated ok!');
            return true;
        }

        $this->warn('Failed to update, hashes don\'t match');
        return false;
    }
}
