<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Models\GoogleWallet\EventObject;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use RuntimeException;

trait HandlesJwtUrls
{
    /**
     * Returns the URL to import this EventObject with.
     * @throws RuntimeException if the $user has not verified their email
     */
    public function getImportUrl(User $user, EventObject $eventObject): string
    {
        throw_unless($user->hasVerifiedEmail(), RuntimeException::class, 'The user must have a verified email address to import an event.');

        $claims = [
            'iss' => $user->email,
            'aud' => 'google',
            'iat' => time(),
            'exp' => time() + 3600,
            'origins' => [parse_url(URL::to('/'), PHP_URL_HOST)],
            'typ' => 'savetowallet',
            'payload' => [
                'eventTicketClasses' => [
                    $eventObject->class->wallet_id,
                ],
                'eventTicketObjects' => [
                    $eventObject->wallet_id,
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
        return JWT::encode($claims, $this->getJwtKey(), 'RS256');
    }

    /**
     * Returns the private key to sign the JWT with.
     */
    private function getJwtKey(): string
    {
        $jwtPath = $this->getJwtUrl();
        throw_unless(file_exists($jwtPath), LogicException::class, 'The JWT file does not exist.');

        $key = json_decode(file_get_contents($jwtPath), true, 16, JSON_THROW_ON_ERROR)['private_key'] ?? null;
        throw_unless($key, LogicException::class, 'The JWT file does not contain a private key.');

        return $key;
    }
}
