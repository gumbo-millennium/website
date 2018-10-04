<?php
declare (strict_types = 1);

namespace App\Services;

use App\Option;

/**
 * Updates and validates WordPress access tokens
 *
 * @author Roelof Roos
 * @license MPL-2.0
 */
class WordPressAccessProvider
{
    /**
     * Public key, used to sign content from Laravel and verify content from WordPress
     */
    const OPTION_PUBLIC = 'laravel-key-public';

    /**
     * Private key, used to sign content from WordPress and verify content from Laravel
     */
    const OPTION_PRIVATE = 'laravel-key-private';

    /**
     * HTTP header containing the content signature
     */
    const SIGNATURE_HEADER = 'X-Signature';

    /**
     * Returns public key
     *
     * @return string|null
     */
    protected function getPublicKey() : ? string
    {
        return Option::get(self::OPTION_PUBLIC);
    }

    /**
     * Returns private key
     *
     * @return string|null
     */
    protected function getPrivateKey() : ? string
    {
        return Option::get(self::OPTION_PRIVATE);
    }

    /**
     * Signs an arbitrary string using the private key
     *
     * @param string|null $content
     * @return string|null Signature, or null if failed
     */
    public function signString(?string $content) : ?string
    {
        $privateKey = $this->getPrivateKey();

        // Abort if empty
        if (empty($privateKey)) {
            return null;
        }

        // Return null if failed
        if (!openssl_sign($content, $signature, $privateKey)) {
            return null;
        }

        return base64_encode($signature);
    }

    /**
     * Validates a string signed with the private key
     *
     * @param string|null $content
     * @param string|null $signature
     * @return bool
     */
    public function validateString(?string $content, ?string $signature) : bool
    {
        // Get public key
        $publicKey = $this->getPublicKey();

        // Always fail if the public key or signature isn't set.
        if ($publicKey === null || empty($signature)) {
            return false;
        }

        if (preg_match('/^[a-z0-9\/+]+\={0,3}$/i', $signature)) {
            $signature = base64_decode($signature, true);
            if ($signature === false) {
                return false;
            }
        }

        // Verifies the signature
        return openssl_verify($content, $signature, $publicKey) === 1;
    }

    /**
     * Validates a request using the stored access token.
     *
     * @param Request $request
     * @return bool
     */
    public function validateRequest(Request $request) : bool
    {
        // Get request body
        return $this->validateString(
            $request->getContent(),
            $request->headers->get(self::SIGNATURE_HEADER)
        );
    }

    /**
     * Sign the response with the private key
     *
     * @param Response $response
     * @return bool
     */
    public function signResponse(Response &$response) : bool
    {
        // Sign content
        $signature = $this->signString($response->getContent());

        // Fail if empty
        if (!$signature) {
            return false;
        }

        // Set header
        $response->header->set(self::SIGNATURE_HEADER, $signature);
        return true;
    }

    /**
     * Regenerates the public and private keys used to sign content.
     *
     * @return int Number of bits in new key
     */
    public function refreshKeys() : bool
    {
        // Build new key
        $keyResource = openssl_pkey_new([
            'private_key_bits' => 4096
        ]);

        // Abort if failed
        if (!is_resource($keyResource)) {
            return false;
        }

        // Get private key
        openssl_pkey_export($keyResource, $privateKey);

        // Get public key
        $keyDetails = openssl_pkey_get_details($keyResource);
        $publicKey = $keyDetails['key'];

        // Free memory
        openssl_pkey_free($keyResource);

        // Store new key
        Option::change(self::OPTION_PUBLIC, $publicKey);
        Option::change(self::OPTION_PRIVATE, $privateKey);

        return true;
    }

    /**
     * Returns the number of bits in the key, or null if unset
     *
     * @return int|null
     */
    public function getKeyBits() : ?int
    {
        // Get public key
        $publicKey = $this->getPublicKey();

        // Abort if empty
        if (empty($publicKey)) {
            return null;
        }

        // Get key resource
        $resource = openssl_get_publickey($publicKey);

        // Abort if failed to read
        if (!is_resource($resource)) {
            return null;
        }

        // Get bits from details
        $keyDetails = openssl_pkey_get_details($resource);
        $keyBits = $keyDetails['bits'];

        // Free resource
        openssl_pkey_free($resource);

        return $keyBits;
    }
}
