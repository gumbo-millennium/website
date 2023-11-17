<?php

declare(strict_types=1);

namespace App\Http\Controllers\Minisite;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;

class SimpleController extends Controller
{
    private readonly string $sitename;

    private readonly array $siteconfig;

    public function __construct(
        Repository $config,
        Request $request
    ) {
        $this->sitename = parse_url($request->getUri(), PHP_URL_HOST);

        $minisites = $config->get('gumbo.minisites', []);
        $this->siteconfig = $minisites[$this->sitename] ?? [
            'enabled' => false,
        ];

        // Attach a middleware to disable the site when, well, disabled.
        $this->middleware(function ($request, $next) {
            if (! $this->siteconfig['enabled']) {
                return Response::view('minisite.disabled', [
                    'sitename' => $this->sitename,
                ], 503);
            }

            return $next($request);
        });
    }

    /**
     * Homepage request.
     */
    public function index(Request $request): HttpResponse
    {
        $page = $this->pageQuery($request)
            ->where('slug', 'home')
            ->first();

        return Response::view('minisite.simple.home', [
            'page' => $page,
        ])->setLastModified($page->updated_at ?? now());
    }

    /**
     * Single page request.
     */
    public function page(Request $request): HttpResponse | RedirectResponse
    {
        $path = trim(parse_url($request->getUri(), PHP_URL_PATH), '/');

        // Redirect requests to the homepage to the homepage
        if ($path === 'home') {
            return Response::redirectTo('/', HttpResponse::HTTP_PERMANENTLY_REDIRECT);
        }

        $page = $this->pageQuery($request)
            ->where('slug', $path)
            ->firstOrFail();

        return Response::view('minisite.simple.page', [
            'page' => $page,
        ])->setLastModified($page->updated_at ?? now());
    }

    private function pageQuery(Request $request): Builder
    {
        return Page::query()->where('group', $this->sitename);
    }
}
