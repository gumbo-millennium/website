<?php

declare(strict_types=1);

namespace App\Http\Policy;

use Illuminate\Http\Request;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Basic as BasicPolicy;
use Spatie\Csp\Value;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base Content-Security-Policy.
 * Allows most local elements and Google Fonts
 */
abstract class BasePolicy extends BasicPolicy
{
    /**
     * Don't act on Nova paths
     *
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function shouldBeApplied(Request $request, Response $response): bool
    {
        // Local checks
        if (app()->isLocal()) {
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
     * Configure CSP directives
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
        if (app()->isProduction()) {
            $this->addDirective(Directive::BLOCK_ALL_MIXED_CONTENT, Value::NO_VALUE);
        }

        // Google Fonts
        $this->addDirective(Directive::STYLE, 'https://fonts.googleapis.com/');
        $this->addDirective(Directive::FONT, 'https://fonts.gstatic.com/');
    }
}
