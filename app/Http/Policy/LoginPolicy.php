<?php

declare(strict_types=1);

namespace App\Http\Policy;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;

/**
 * Login form Content-Security-Policy.
 * Disallows all Javascript.
 */
class LoginPolicy extends BasePolicy
{
    /**
     * Configure CSP directives.
     *
     * @return void
     */
    public function configure()
    {
        // Use basic handler
        parent::configure();

        // Reset scripts to a small subset
        $this
            ->purgeDirective(Directive::SCRIPT)
            ->addDirective(Directive::SCRIPT, Keyword::SELF)
            ->addDirective(Directive::SCRIPT, Keyword::UNSAFE_EVAL)
            ->addDirective(Directive::SCRIPT, URL::to('/'));

        // Allow data:-images
        $this->addDirective(Directive::IMG, 'data:');

        if (App::isProduction() || ! App::hasDebugModeEnabled()) {
            $this->addNonceForDirective(Directive::SCRIPT);

            return;
        }

        // Allow unsafe inline
        $this->addDirective(Directive::SCRIPT, Keyword::UNSAFE_INLINE);

        // Allow data images
        $this->addDirective(Directive::IMG, 'data:');
    }
}
