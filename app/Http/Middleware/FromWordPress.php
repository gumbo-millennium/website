<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Handles requests coming in from WordPress, by validating if it's JSON and if the content
 * signature is valid.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FromWordPress
{
    /**
     * Validate the content of an inbound request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->isJson()) {
            throw new BadRequestHttpException('Expected content to be JSON');
        }

        if (!$this->isAuthorized($request)) {
            throw new UnauthorizedHttpException('WordPress', 'Requires authentication');
        }

        $next($request);
    }

    /**
     * Checks if the user is authorized to make this call
     *
     * @param Request $request
     * @return bool
     */
    protected function isAuthorized(Request $request): bool
    {
        // Get authentication header
        $authHeader = $request->headers->get('Authenticate');
        $authHeaderBits = explode(' ', $authHeader, 2);

        // Check for correct method
        if ($authHeaderBits[0] !== 'WordPress' || count($authHeaderBits) != 2) {
            return false;
        }

        // Token to authenticate with
        $authToken = end($authHeaderBits);

        // Get secret
        $secret = Option::get(Option::OPTION_AUTH_TOKEN);

        // Fail if the secret is missing, and create one
        if (!$secret) {
            $this->makeNewSecret();
            return false;
        }

        // Fail if the secrets don't match
        if ($authToken !== $secret) {
            return false;
        }

        // Change secret
        $this->makeNewSecret();

        // Return OK
        return true;
    }

    /**
     * Generates a new shared secret
     *
     * @return void
     */
    protected function makeNewSecret(): void
    {
        Option::set(Option::OPTION_AUTH_TOKEN, str_random(40));
    }
}
