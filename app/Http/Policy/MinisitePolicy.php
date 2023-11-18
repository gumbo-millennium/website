<?php

declare(strict_types=1);

namespace App\Http\Policy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Spatie\Csp\Directive;

/**
 * Minisite Content-Security-Policy.
 * Allows everything the AppPolicy allows, but explicilty adds app.url.
 */
class MinisitePolicy extends AppPolicy
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

        $urlsToAdd = Collection::make([
            config('app.url'),
            config('app.asset_url'),
            config('app.mix_url'),
        ])->map(function ($row) {
            if ($row && $url = parse_url($row)) {
                return "{$url['scheme']}://{$url['host']}";
            }

            return null;
        })->filter();

        foreach ($urlsToAdd as $url) {
            $this->addDirective(Directive::CONNECT, $url);
            $this->addDirective(Directive::IMG, $url);
            $this->addDirective(Directive::SCRIPT, $url);
            $this->addDirective(Directive::STYLE, $url);
            $this->addDirective(Directive::WORKER, $url);
        }
    }
}
