<?php

declare(strict_types=1);

namespace App\Http\Policy;

use Illuminate\Http\Request;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base Content-Security-Policy.
 * Allows most local elements, Google Fonts and our Service Worker
 */
class AppPolicy extends BasePolicy
{
    private const IGNORED_PATHS = '/^\/?admin($|\/)/';

    /**
     * Don't act on Nova paths
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function shouldBeApplied(Request $request, Response $response): bool
    {
        // Don't apply on Nova
        if (preg_match(self::IGNORED_PATHS, $request->path())) {
            return false;
        }

        // Forward
        return parent::shouldBeApplied($request, $response);
    }

    /**
     * Configure CSP directives
     * @return void
     */
    public function configure()
    {
        // Use basic handler
        parent::configure();

        // Allow Service Worker
        $this->addDirective(Directive::WORKER, Keyword::SELF);

        // Allow data: images
        $this->addDirective(Directive::IMG, 'data:');

        // Allow Stripe endpoints
        $this->addDirective(Directive::CONNECT, 'https://api.stripe.com');

        // Load local stuff, if local
        if (app()->isLocal()) {
            $this->configureLocal();
        }
    }

    /**
     * Configure local directives
     * @return void
     */
    public function configureLocal(): void
    {
        $localUrls = [
            '127.8.8.8',
            'localhost:8080',
            'localhost:3000',
        ];

        $localProtos = ['http://', 'ws://'];

        foreach ($localUrls as $url) {
            foreach ($localProtos as $proto) {
                $this->addDirective(Directive::CONNECT, $proto . $url);
                $this->addDirective(Directive::IMG, $proto . $url);
                $this->addDirective(Directive::SCRIPT, $proto . $url);
                $this->addDirective(Directive::STYLE, $proto . $url);
                $this->addDirective(Directive::WORKER, $proto . $url);
            }
        }
    }
}
