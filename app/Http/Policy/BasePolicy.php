<?php

declare(strict_types=1);

namespace App\Http\Policy;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Policy;
use Spatie\Csp\Value;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base Content-Security-Policy.
 * Allows most local elements and Google Fonts.
 */
abstract class BasePolicy extends Policy
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
        // Allow self for all
        $this
            ->addDirective(Directive::BASE, Keyword::SELF)
            ->addDirective(Directive::CONNECT, Keyword::SELF)
            ->addDirective(Directive::DEFAULT, Keyword::SELF)
            ->addDirective(Directive::FORM_ACTION, Keyword::SELF)
            ->addDirective(Directive::IMG, Keyword::SELF)
            ->addDirective(Directive::MEDIA, Keyword::SELF)
            ->addDirective(Directive::OBJECT, Keyword::NONE)
            ->addDirective(Directive::SCRIPT, Keyword::SELF)
            ->addDirective(Directive::STYLE, Keyword::SELF);

        // Allow manifests too
        $this->addDirective(Directive::MANIFEST, Keyword::SELF);

        // Allow unsafe-eval, required for AlpineJS
        $this->addDirective(Directive::SCRIPT, Keyword::UNSAFE_EVAL);

        // Allow unsafe-inline, required for Vue
        $this->addDirective(Directive::SCRIPT, Keyword::UNSAFE_INLINE);
        $this->addDirective(Directive::STYLE, Keyword::UNSAFE_INLINE);

        // Add Google Fonts
        $this->addDirective(Directive::STYLE, 'https://fonts.googleapis.com/');
        $this->addDirective(Directive::FONT, 'https://fonts.gstatic.com/');

        // Add app.url and app.asset_url
        $this->addApplicationUrlExceptions();

        // Prevent mixed content from loading on non-local environment (local usually has no HTTPS)
        if (! App::isLocal()) {
            $this->addDirective(Directive::BLOCK_ALL_MIXED_CONTENT, Value::NO_VALUE);
        }

        // Add debugbar support (data:-fonts)
        if (App::hasDebugModeEnabled()) {
            $this->addDirective(Directive::FONT, 'data:');
        }
    }

    /**
     * Removes all policies for a given directive.
     */
    protected function purgeDirective(string $directive): self
    {
        $this->directives[$directive] = [];

        return $this;
    }

    /**
     * Adds URLs that content may be served from.
     */
    private function addApplicationUrlExceptions(): void
    {
        $requestDomain = parse_url(Request::url(), PHP_URL_HOST);

        $appUrls = array_filter([
            Config::get('app.url') => true,
            Config::get('app.asset_url') => false,
        ]);

        // Add asset host in case it's different from the current request
        foreach ($appUrls as $appUrl => $interactive) {
            if (parse_url($appUrl) === $requestDomain) {
                continue;
            }

            $cspRoot = sprintf(
                '%s://%s',
                parse_url($appUrl, PHP_URL_SCHEME),
                parse_url($appUrl, PHP_URL_HOST),
            );

            $this->addDirective(Directive::DEFAULT, $cspRoot);
            $this->addDirective(Directive::STYLE, $cspRoot);
            $this->addDirective(Directive::IMG, $cspRoot);
            $this->addDirective(Directive::SCRIPT, $cspRoot);

            // Also add for connect and form-action for interactive domains
            if ($interactive) {
                $this->addDirective(Directive::CONNECT, $cspRoot);
                $this->addDirective(Directive::FORM_ACTION, $cspRoot);
            }
        }
    }
}
