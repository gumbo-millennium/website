<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use JsonException;
use LauLamanApps\IzettleApi\Client\AccessToken;
use LauLamanApps\IzettleApi\GuzzleIzettleClient as IZettleClient;
use RuntimeException;

class InventoryService
{
    private const CACHE_TOKEN = 'izettle.api.token';

    private string $clientId;

    private string $clientAssertion;

    private ?IZettleClient $client = null;

    public function __construct()
    {
        $clientId = Config::get('services.izettle.client-id');
        $clientAssertion = Config::get('services.izettle.client-assertion');

        if (! $clientId || ! $clientAssertion) {
            throw new RuntimeException('iZettle API is not configured');
        }

        $this->clientId = $clientId;
        $this->clientAssertion = $clientAssertion;
    }

    /**
     * Get JSON from the API.
     */
    public function getJson(string $endpoint, array $args = []): array
    {
        // Get client
        $response = $this->getClient()->get($endpoint, $args);

        // Throw exception if response is not 200
        if ($response === null) {
            throw new RuntimeException("Inventory API call to {$endpoint} failed.");
        }

        // Get body
        $contents = (string) $response?->getBody();

        try {
            return json_decode($contents, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    public function retrieveCategories(): Collection
    {
        // Get API
        $prodApi = $this->getProductsClient();

        // Categories
        $categories = $prodApi->getLibrary();

        return collect($categories);
    }

    /**
     * Returns assertion-based client, caches access tokens.
     */
    public function getClient(): IZettleClient
    {
        if ($this->client) {
            return $this->client;
        }

        // Get guzzle token
        $guzzle = new GuzzleClient([
            \GuzzleHttp\RequestOptions::HTTP_ERRORS => true,
        ]);
        $izettleClient = new IZettleClient($guzzle, $this->clientId, $this->clientAssertion);

        // Check cache for access token
        $accessToken = $this->getAccessToken();
        if ($accessToken) {
            $izettleClient->setAccessToken($accessToken);

            return $izettleClient;
        }

        // Get token from assertion
        $assertion = Config::get('services.izettle.client-assertion');
        $accessToken = $izettleClient->getAccessTokenFromApiTokenAssertion($assertion);

        // Store token to cache, auto-removing when expired
        Cache::put(self::CACHE_TOKEN, $accessToken, $accessToken->getExpires());

        return $izettleClient;
    }

    protected function getAccessToken(): ?AccessToken
    {
        $accessToken = Cache::get(self::CACHE_TOKEN);
        if (! $accessToken instanceof AccessToken || $accessToken->isExpired()) {
            return null;
        }

        return $accessToken;
    }
}
