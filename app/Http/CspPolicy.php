<?php

namespace App\Http;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Basic;
use Spatie\Csp\Policies\Policy;

class CspPolicy extends Basic
{
    /**
     * Configure the Content-Security-Policy
     *
     * @return void
     */
    public function configure()
    {
        // Let the basic policy go first
        parent::configure();

        // Allow this domain to determine some stuff
        $this->addDirective(Directive::FONT, Keyword::SELF);

        // Allow Google Fonts
        $this->addDirective(Directive::STYLE, 'https://fonts.googleapis.com/');
        $this->addDirective(Directive::FONT, 'https://fonts.gstatic.com/');

        // Allow Stripe endpoints
        $this->addDirective(Directive::FRAME, ['https://js.stripe.com', 'https://hooks.stripe.com']);
        $this->addDirective(Directive::CONNECT, 'https://api.stripe.com');
        $this->addDirective(Directive::SCRIPT, 'https://js.stripe.com');
    }
}
