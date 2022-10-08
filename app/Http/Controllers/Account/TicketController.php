<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\Google\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;

class TicketController extends Controller
{
    public function index(Request $request, WalletService $walletService)
    {
        $user = $request->user();
        $showOld = (bool) $request->query('include-past', false);

        $activities = Activity::query()
            ->whereAvailable($user)
            ->unless($showOld, fn ($query) => $query->where([
                ['end_date', '>=', Date::now()->subDay()],
            ]))
            ->whereHas('enrollments', fn ($query) => $query->where('user_id', $user->id)->active())
            ->with('enrollments', fn ($query) => $query->where('user_id', $user->id)->active())
            ->get()
            ->sortBy('start_date')
            ->each(fn (Activity & $activity) => $activity->enrollment = $activity->enrollments->first());

        $googleWalletUrls = Collection::make();
        if ($walletService->isEnabled()) {
            foreach ($activities->where('end_date', '>', Date::now()) as $activity) {
                if (! $activity->enrollment) {
                    continue;
                }

                // Find event object
                $importUrl = $walletService->getImportUrl($user, $activity->enrollment);
                if ($importUrl) {
                    $googleWalletUrls->put($activity->id, $importUrl);
                }
            }
        }

        return Response::view('account.tickets', [
            'activities' => $activities,
            'googleWalletUrls' => $googleWalletUrls,
        ]);
    }
}
