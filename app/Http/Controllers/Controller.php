<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Policy\AppPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Policy;

/**
 * Main controller class
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Returns a policy you can add stuff to
     */
    protected function alterCspPolicy(): Policy
    {
        if (App::has(AppPolicy::class)) {
            return App::make(AppPolicy::class);
        }

        $instance = App::make(AppPolicy::class);
        App::instance(AppPolicy::class, $instance);
        return $instance;
    }
    /**
     * Whitelists the given image URLs with the image content policy
     */
    protected function addImageUrlsToCspPolicy(iterable $imageUrls): void
    {
        $hosts = [];
        foreach ($imageUrls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $urlSchema = parse_url($url);
            $hosts[] = sprintf('%s://%s', $urlSchema['scheme'], $urlSchema['host']);
        }

        $this->alterCspPolicy()
            ->addDirective(Directive::IMG, array_unique($hosts));
    }
}
