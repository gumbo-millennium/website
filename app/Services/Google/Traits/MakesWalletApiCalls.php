<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use Google\Client as GoogleClient;
use GuzzleHttp\ClientInterface as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;
use JsonException;

trait MakesWalletApiCalls
{
    protected ?GuzzleClient $googleHttpClient = null;

    protected GoogleClient $googleClient;

    /**
     * Returns an authorized Guzzle Client.
     */
    protected function getGoogleClient(): GuzzleClient
    {
        return ($this->googleHttpClient ??= $this->googleClient->authorize());
    }

    /**
     * Makes the given request, ensures HTTP errors are thrown on non-2xx responses.
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function sendRequest(string $method, string $url, array $options = []): mixed
    {
        // Convert body to JSON
        if (isset($options['body']) && ! is_scalar($options['body'])) {
            $options['body'] = json_encode($options['body']);
        }

        // Ensure non-2xx responses are thrown as exceptions
        $options['http_errors'] = true;

        // Make the request
        $response = $this->getGoogleClient()->request($method, $url, $options);

        // Decode the JSON
        return json_decode($response->getBody()->getContents(), true, 64, JSON_THROW_ON_ERROR);
    }

    /**
     * Autoload Google Wallet API client.
     */
    private function initializeMakesWalletApiCalls(): void
    {
        $this->googleClient = App::make('google_wallet_api');
    }
}
