<?php

declare(strict_types=1);

namespace App\Http\Policy;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;

/**
 * Login form Content-Security-Policy.
 * Disallows all Javascript
 */
class LoginPolicy extends BasePolicy
{
    /**
     * Configure CSP directives
     * @return void
     */
    public function configure()
    {
        // Use basic handler
        parent::configure();

        // Disable all scripts
        $this->directives[Directive::SCRIPT] = [Keyword::NONE];
    }
}
