<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use GuzzleHttp\ClientInterface as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;
use JsonException;

trait MakesWalletApiCalls
{
    protected ?GuzzleClient $googleHttpClient = null;

    abstract public function isEnabled(): bool;

    /**
     * Returns an authorized Guzzle Client.
     */
    protected function getGoogleClient(): GuzzleClient
    {
        throw_unless($this->isEnabled(), RuntimeException::class, 'Google Wallet service is diabled');

        if (! $this->googleHttpClient) {
            $googleClient = App::make('google_wallet_api');
            $this->googleHttpClient = $googleClient->authorize();
        }

        return $this->googleHttpClient;
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
}
