<?php

declare(strict_types=1);

namespace App\Http\Controllers\Traits;

use Spatie\Csp\Directive;

trait MutatesResponseCsp
{
    protected function addToCsp(iterable $items, string $directive = Directive::IMG): void
    {
        $hosts = [];
        foreach ($items as $url) {
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $urlSchema = parse_url($url);
            $hosts[] = sprintf('%s://%s', $urlSchema['scheme'], $urlSchema['host']);
        }

        $this->alterCspPolicy()
            ->addDirective($directive, array_unique($hosts));
    }
}
