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

        // Disable all scripts
        $this->directives[Directive::SCRIPT] = [];

        // Add some exceptions
        $this->addDirective(Directive::SCRIPT, Keyword::SELF);
        $this->addDirective(Directive::SCRIPT, Keyword::UNSAFE_EVAL);
        $this->addDirective(Directive::SCRIPT, rtrim(URL::to('/'), '/'));

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
