<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WordPressAccessProvider;

class RefreshWordPressCertificate extends Command
{
    /**
     * Dummy content used to test signatures
     */
    const SIGN_CONTENT = <<<SIGN
Vel quidem soluta rerum. Aut asperiores eum ullam sunt sequi. Soluta quae autem
velit aspernatur magni velit omnis ut. Molestias mollitia ratione voluptatem.
Reprehenderit beatae quaerat similique voluptatem earum sint accusamus dolore.
Et nulla et debitis nemo est qui non eveniet. Enim ut sint et et. Explicabo qui
consequatur id ab rerum eveniet. Quis eius dolore numquam ad expedita. Omnis sed
assumenda ullam. Et sed fugit velit labore dolores. Perferendis tenetur rem
incidunt possimus. Sunt asperiores inventore non sapiente in. Est doloribus et
quos enim et. Repellendus facilis ut minus minima est non vel aut.
SIGN;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:wp-key {--f|force : Create a new key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles key generation and validation for WordPress ↔ Laravel communication';

    /**
     * WordPress key provider
     *
     * @var WordPressAccessProvider
     */
    protected $provider;

    /**
     * Create a new command instance.
     *
     * @param WordPressAccessProvider $provider
     * @return void
     */
    public function __construct(WordPressAccessProvider $provider)
    {
        parent::__construct();

        $this->provider = $provider;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('force')) {
            $this->generateKey();
        }
        $this->validateKey();
    }

    /**
     * Generates a new key
     *
     * @return bool
     */
    protected function generateKey() : bool
    {
        $this->comment('Building new key...');
        $this->line('Generating new key, this might take a while...');
        if (!$this->provider->refreshKeys()) {
            $this->error('Failed to refresh key!');
            return false;
        }

        $this->line(sprintf(
            'Created new public/private key pair with <info>%d</> bits',
            $this->provider->getKeyBits()
        ));

        return true;
    }

    /**
     * Validates current key
     *
     * @return bool
     */
    protected function validateKey() : bool
    {
        $content = self::SIGN_CONTENT;
        $this->comment('Validating certificate use...');
        $this->line(sprintf('Signing content of <info>%.2fkb</info>.', mb_strlen($content, 'UTF-8') / 1024));
        $signature = $this->provider->signString($content);

        if (empty($signature)) {
            $this->error('Failed to get signature');
            return false;
        }

        $this->line(sprintf('Got signature of <info>%.2fkb</info>.', mb_strlen($signature, 'UTF-8') / 1024));

        $this->line('Validating signature');
        if (!$this->provider->validateString($content, $signature)) {
            $this->error('Failed to validate signature');
            return false;
        }

        $this->info('Test OK');
        return true;
    }
}
