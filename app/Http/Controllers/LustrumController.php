<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Page;
use App\Models\Role;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class LustrumController extends Controller
{
    public function index(Request $request): HttpResponse
    {
        $lustrumRoot = sprintf('https://%s', Config::get('gumbo.lustrum-domains')[0]);
        if (App::environment('local')) {
            $lustrumRoot = sprintf('http://%s', $request->getHost());
        }

        // Ensure assets load locally, but all links are egress

        Config::set('app.mix_url', $lustrumRoot);
        Config::set('app.asset_url', $lustrumRoot);

        URL::forceRootUrl(Config::get('app.url'));

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

        SEOMeta::setCanonical($lustrumRoot);

        return Response::view('minisite.lustrum', [
            'lustrumNav' => true,
            'activities' => $activities,
            'page' => Page::query()
                ->where('slug', 'lustrum')
                ->first(),
        ]);
    }
}
