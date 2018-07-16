<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Creates the environment file
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class CreateEnvCommand extends Command
{
    /**
     * Environment template file's name
     */
    const SOURCE_FILE_NAME = '.env.example';

    /**
     * IP Address of Docker container
     */
    const DOCKER_HOST = "127.13.37.1";

    /**
     * Default (dev) config
     */
    const DEFAULT_CONFIGS = [
        'APP_KEY' => null,
        'DB_DATABASE' => 'laravel',
        'BROADCAST_DRIVER' => 'redis',
        'CACHE_DRIVER' => 'redis',
        'SESSION_DRIVER' => 'redis',
        'QUEUE_DRIVER' => 'redis',
        'WP_DATABASE' => 'wordpress',
    ];

    /**
     * Custom configs for certain environments
     *
     * @var string
     */
    const EXTRA_CONFIGS = [
        'local' => [
            // Set app location
            'APP_URL' => 'http://{HOST}:8080',

            // Set hosts to Docker
            'DB_HOST' => '{HOST}',
            'WP_HOST' => '{HOST}',
            'REDIS_HOST' => '{HOST}',
            'MAIL_HOST' => '{HOST}',
            'MAIL_PORT' => '1025',

            // Add user and password
            'DB_USERNAME' => 'laravel',
            'DB_PASSWORD' => 'kiepo9Eeth0hoech5ooLa6ed8oaphaih',
            'WP_USERNAME' => 'corcel',
            'WP_PASSWORD' => 'zie2doboveesh2IraiDaim1Daiku6ue9',
        ],
        'travis' => [
            'APP_ENV' => 'testing',

            // Set DB access to travis user
            'DB_USERNAME' => 'travis',
            'DB_PASSWORD' => null,
            'WP_USERNAME' => 'travis',
            'WP_PASSWORD' => null,

            // Disable mail, send it to an array
            'MAIL_DRIVER' => 'array'
        ]
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:env
                            {env? : Environment to create}
                            {--f|force : Force the creation of the env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialises the .env file';

    /**
     * Writes an .env file,
     *
     * @return mixed
     */
    public function handle()
    {
        // Find app root dir
        $envFile = base_path('.env');
        $sourceFile = base_path('.env.example');
        $forced = $this->option('force');

        // Create file if it does not exist.
        if ((!file_exists($envFile) || $forced) && is_writeable(dirname($envFile))) {
            $sourceFileName = $this->argument('env', 'staging');

            $config = $this->buildEnvironmentConstructionConfig();
            $this->line('<info>Environment configuration ready</>');

            $content = $this->constructEnv($sourceFile, $config);
            $this->line('<info>Environment constructed</>');

            $ok = $this->writeEnvFile($envFile, $content);
            $this->line('<info>Environment written to .env</>');
        }

        if (empty(config('app.key')) || $forced) {
            // Generate app key
            $this->line('Generating key...');
            $this->call('key:generate', []);
        }
    }

    /**
     * Constructs a collection for the current environment.
     *
     * @return Collection
     */
    protected function buildEnvironmentConstructionConfig() : Collection
    {
        $env = $this->argument('env') ?? 'local';

        $this->line(sprintf(
            'Building configuration for "<comment>%s</>" environment.',
            $env
        ));

        if (!isset(self::EXTRA_CONFIGS[$env])) {
            throw new \InvalidArgumentException("Environment {$env} not found!");
        }

        return collect(array_merge(self::DEFAULT_CONFIGS, self::EXTRA_CONFIGS[$env]));
    }

    /**
     * Builds content of env file, replacing keys from the example with the new values specified in CONFIGS.
     *
     * @param string $source
     * @return string .env file content
     */
    protected function constructEnv(string $source, Collection $config) : string
    {
        $this->line(sprintf(
            'Building new .env file from <comment>%s</>',
            $source
        ));

        $template = explode("\n", file_get_contents($source));
        $result = [];
        $replace = 0;

        foreach ($template as $line) {
            $line = trim($line);
            if (!preg_match('/^([A-Z][A-Z0-9_]+)=(?:.*)$/', $line, $matches)) {
                $result[] = $line;
                continue;
            }

            // Get key
            $key = $matches[1];

            // Replace line, if found
            if ($config->has($key) && $config->get($key) !== '(unset)') {
                $line = sprintf(
                    '%s=%s',
                    $key,
                    str_replace('{HOST}', self::DOCKER_HOST, $config->get($key))
                );
                $replace++;
            }

            // Add line
            $result[] = $line;
        }

        $this->line(sprintf(
            'Replaced <comment>%d</comment> of <comment>%d</> entries for target file.',
            $replace,
            count($result)
        ));

        return implode("\n", $result);
    }

    /**
     * Writes the .env file in a safe fashion
     *
     * @param string $file File to write (usually /.env)
     * @param string $content Contents to write
     * @return bool True if write was performed OK
     */
    protected function writeEnvFile(string $file, string $content) : bool
    {
        $this->line(sprintf(
            'Creating .env file of <comment>%.1f</> KB in "<info>%s</>".',
            mb_strlen($content, '8bit') / 1024,
            dirname($file)
        ));

        // Make sure file exists
        touch($file);

        // Write file
        return file_put_contents($file, $content) === mb_strlen($content, '8bit');
    }
}
