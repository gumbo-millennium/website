<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Page;
use App\Models\Role;
use Artesaos\SEOTools\Facades\SEOMeta;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class LustrumController extends Controller
{
    public function index(Request $request): HttpResponse
    {
        $rootUrl = sprintf('https://%s', Config::get('gumbo.lustrum-domains')[0]);
        if (! App::environment('local')) {
            URL::forceRootUrl($rootUrl);
        }

        try {
            $activityHost = Role::findByName('lucie');

            $activities = Activity::query()
                ->whereHas('role', fn (Builder $query) => $query->where('role.id', $activityHost->id))
                ->whereAvailable()
                ->where('start_date', '>', now())
                ->whereNull('cancelled_at')
                ->orderBy('start_date')
                ->take(2)
                ->get();
        } catch (RoleDoesNotExist $roleNotFoundError) {
            // Present an empty array of activities
            $activities = Collection::make();
        }

        SEOMeta::setCanonical(
            (string) Uri::fromParts([
                'scheme' => parse_url($rootUrl, PHP_URL_SCHEME),
                'host' => parse_url($rootUrl, PHP_URL_HOST),
                'path' => '/',
            ])
        );

        return Response::view('minisite.lustrum', [
            'activities' => $activities,
            'page' => Page::query()
                ->where('slug', 'lustrum')
                ->first(),
        ]);
    }
}
