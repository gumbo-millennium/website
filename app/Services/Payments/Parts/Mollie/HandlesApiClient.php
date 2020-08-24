<?php

declare(strict_types=1);

namespace App\Services\Payments\Parts\Mollie;

use App\Helpers\Str;
use Illuminate\Support\Facades\Config;
use Mollie\Api\MollieApiClient;
use RuntimeException;

/**
 * Handles retrieval and parsing of Mollie payment methods
 */
trait HandlesApiClient
{
    /**
     * Mollie API client
     * @var MollieApiClient
     */
    private ?MollieApiClient $apiClient = null;

    /**
     * Returns the Mollie API
     * @return MollieApiClient
     */
    protected function getMollieApi(): MollieApiClient
    {
        // Skip if already created
        if ($this->apiClient) {
            return $this->apiClient;
        }

        // Get config
        $apiKey = (string) Config::get('services.mollie.api-key', null);
        $isTestMode = (bool) Config::get('services.mollie.test-mode', false);

        // Validate API key is set
        if (empty($apiKey)) {
            throw new RuntimeException('No Mollie API key provided');
        }

        // Test mode safety net
        if ($isTestMode && !Str::startsWith($apiKey, 'test_')) {
            throw new RuntimeException('Test mode is enabled, but no test API token is provided');
        }

        // Create client
        $this->apiClient = new MollieApiClient();
        $this->apiClient->setApiKey($apiKey);
        return $this->apiClient;
    }
}
