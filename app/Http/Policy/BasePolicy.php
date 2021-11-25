<?php

declare(strict_types=1);

namespace App\Http\Policy;

use App\Helpers\Str;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Basic as BasicPolicy;
use Spatie\Csp\Value;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base Content-Security-Policy.
 * Allows most local elements and Google Fonts.
 */
abstract class BasePolicy extends BasicPolicy
{
    /**
     * Don't act on Nova paths.
     */
    public function shouldBeApplied(HttpRequest $request, Response $response): bool
    {
        // Local checks
        if (App::isLocal()) {
            // Dont apply on Whoops
            if (property_exists($response, 'exception') && $response->exception) {
                return false;
            }

            // Don't apply when running hot
            if (file_exists(public_path('/hot'))) {
                return false;
            }
        }

        // Forward
        return parent::shouldBeApplied($request, $response);
    }

    /**
     * Configure CSP directives.
     *
     * @return void
     */
    public function configure()
    {
        // Use basic handler
        parent::configure();

        // Allow manifest
        $this->addDirective(Directive::MANIFEST, Keyword::SELF);

        // Prevent mixed content from loading on production (testing has no HTTPS)
        if (App::isProduction()) {
            $this->addDirective(Directive::BLOCK_ALL_MIXED_CONTENT, Value::NO_VALUE);
        }

        // Get URLs
        $appUrl = Config::get('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);

        $assetUrl = Config::get('app.asset_url') ?? $appUrl;
        $assetHost = parse_url($assetUrl, PHP_URL_HOST);

        $requestHost = Request::getHost() ?? Request::getHttpHost() ?? $appHost;

        // Add asset host in case it's different from the current request
        if ($assetHost !== $requestHost) {
            $assetCspRoot = sprintf(
                '%s://%s',
                parse_url($assetUrl, PHP_URL_SCHEME),
                parse_url($assetUrl, PHP_URL_HOST),
            );

            $this->addDirective(Directive::DEFAULT, $assetCspRoot);
            $this->addDirective(Directive::STYLE, $assetCspRoot);
            $this->addDirective(Directive::IMG, $assetCspRoot);
            $this->addDirective(Directive::SCRIPT, $assetCspRoot);
        }

        // Add the main host in case it's different from the current request
        if ($appHost !== $requestHost) {
            $appCspRoot = sprintf(
                '%s://%s',
                parse_url($appUrl, PHP_URL_SCHEME),
                parse_url($appUrl, PHP_URL_HOST),
            );

            $this->addDirective(Directive::CONNECT, $appCspRoot);
            $this->addDirective(Directive::FORM_ACTION, $appCspRoot);
        }

        // Google Fonts
        $this->addDirective(Directive::STYLE, 'https://fonts.googleapis.com/');
        $this->addDirective(Directive::FONT, 'https://fonts.gstatic.com/');

        // Local
        if (App::isLocal()) {
            $this->removeNoncesForLocalDevelopment();
        }
    }

    private function removeNoncesForLocalDevelopment(): void
    {
        // Remove nonces
        foreach ($this->directives as $directive => $values) {
            $this->directives[$directive] = array_filter(
                $values,
                fn ($val) => ! Str::contains($val, 'nonce-'),
            );
        }

        // Add debugbar nonces
        if (! class_exists(\DebugBar\DebugBar::class)) {
            return;
        }

        $this->addDirective(Directive::SCRIPT, Keyword::UNSAFE_EVAL);
        $this->addDirective(Directive::SCRIPT, Keyword::UNSAFE_INLINE);

        $this->addDirective(Directive::STYLE, Keyword::UNSAFE_INLINE);

        $this->addDirective(Directive::FONT, 'data:');
    }
}
