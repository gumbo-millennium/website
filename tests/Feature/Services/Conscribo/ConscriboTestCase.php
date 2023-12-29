<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Conscribo;

use App\Services\Conscribo\Client;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConscriboTestCase extends TestCase
{
    protected ConfigRepository $config;

    protected CacheRepository $cache;

    /**
     * @before
     */
    public function prepareTest(): void
    {
        $this->afterApplicationCreated(fn () => Http::preventStrayRequests());

        $this->config = new ConfigRepository([
            'conscribo' => [
                // Host
                'base_url' => 'https://secure.example.com',
                'account' => 'testing',

                // Auth
                'username' => 'Username',
                'password' => 'Password',

                // Resources
                'user_resource' => 'user',
            ],
        ]);

        $this->cache = new CacheRepository(new ArrayStore());
    }

    protected function getClient(): Client
    {
        return new Client($this->cache, $this->config);
    }
}
