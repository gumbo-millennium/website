<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Helpers\Arr;
use App\Models\GoogleWallet\EventObject;
use Firebase\JWT\JWT;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use LogicException;
use RuntimeException;

trait HandlesJwtUrls
{
    private ?Collection $cachedCredentialData = null;

    /**
     * Returns the URL to import this EventObject with.
     * @throws RuntimeException if the $user has not verified their email
     */
    public function getImportUrl(EventObject $eventObject): string
    {
        $ticketObjectData = $this->buildTicketObjectData($eventObject);
        $ticketClassData = $this->buildTicketClassData($eventObject->class);

        $minimalObjectData = Arr::only($ticketObjectData, ['id', 'classId', 'ticketHolderName', 'ticketNumber', 'ticketType', 'state', 'barcode', 'validTimeInterval']);
        $minimalClassData = Arr::only($ticketClassData, ['id', 'eventId', 'eventName', 'dateTime', 'callbackOptions']);

        $claims = [
            'iss' => $this->getServiceAccountData('client_email'),
            'aud' => 'google',
            'origins' => [parse_url(URL::to('/'), PHP_URL_HOST)],
            'typ' => 'savetowallet',
            'payload' => [
                'eventTicketClasses' => [
                    $minimalClassData,
                ],
                'eventTicketObjects' => [
                    $minimalObjectData,
                ],
            ],
        ];

        return "https://pay.google.com/gp/v/save/{$this->signClaims($claims)}";
    }

    /**
     * Returns the URL to the JWT file.
     */
    protected function getJwtUrl(): string
    {
        return Config::get('services.google.wallet.key_file');
    }

    /**
     * Returns the URL to the JWT file.
     */
    protected function signClaims(array $claims): string
    {
        return JWT::encode($claims, $this->getServiceAccountData('private_key'), 'RS256');
    }

    /**
     * Returns the private key to sign the JWT with.
     */
    private function getServiceAccountData(string $key): string
    {
        throw_unless(in_array($key, ['client_email', 'private_key'], true), LogicException::class, 'The key must be either client_email or private_key.');

        if (! $this->cachedCredentialData) {
            $jwtPath = $this->getJwtUrl();
            throw_unless(file_exists($jwtPath), LogicException::class, 'The JWT file does not exist.');

            $this->cachedCredentialData = Collection::make(json_decode(file_get_contents($jwtPath), true, 16, JSON_THROW_ON_ERROR));
        }

        throw_unless($this->cachedCredentialData->has($key), LogicException::class, "The JWT file does not contain the key {$key}.");

        return $this->cachedCredentialData->get($key);
    }
}
