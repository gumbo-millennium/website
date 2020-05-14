<?php

declare(strict_types=1);

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Activities\Traits\HandlesSettingMetadata;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use App\ViewModels\ActivityViewModel;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles showing activity lists, activities and the schedule route
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class DisplayController extends Controller
{
    use HandlesSettingMetadata;

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Get the requesting user
        $user = $request->user();

        // Get all future events by default
        $query = Activity::getNextActivities($user);

        // Get all past events instead
        if ($request->has('past')) {
            $query = Activity::query()
                ->where('end_date', '<=', now())
                ->whereAvailable($user)
                ->orderByDesc('end_date');
        }

        // Paginate the response
        $activities = $query->simplePaginate(9);

        // Collect an empty list of enrollments
        $enrollments = collect();

        if ($user) {
            // Get all user enrollments, indexed by the activity_id
            $enrollments = Enrollment::query()
                ->whereUserId($request->user()->id)
                ->whereIn('activity_id', $activities->pluck('id'))
                ->orderBy('created_at', 'asc')
                ->get()
                ->keyBy('activity_id');
        }

        // SEO
        $title = 'Activiteiten';
        $description = "Ga mee op avontuur met Gumbo Millennium en kom naar onze feesten en activiteiten.";
        $canonical = route('activity.index');

        // Set SEO data
        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical($canonical);

        // Set Open Graph
        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl($canonical);

        // Set JSON+LD
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::setUrl($canonical);

        // Render the view with the events and their enrollments
        return view('activities.index', [
            'activities' => $activities,
            'enrollments' => $enrollments,
            'past' => $request->has('past')
        ]);
    }

    /**
     * Display the specified resource.
     * @param  Activity  $activity
     * @return Response
     */
    public function show(Request $request, Activity $activity)
    {
        // Ensure the user can see this
        $this->authorize('view', $activity);

        // Load enrollments
        $activity->load(['enrollments']);

        // Set meta
        $this->setActivityMeta($activity);

        // Show view
        return view('activities.show', new ActivityViewModel(
            $request->user(),
            $activity
        ));
    }

    /**
     * Handle "please login to enroll" buttons
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse
     */
    public function login(Request $request, Activity $activity): RedirectResponse
    {
        // Redirect if user is already logged in, or returning here
        if ($request->user()) {
            return redirect()
                ->route('activity.show', ['activity' => $activity]);
        }

        // Redirect to login with a backlink here
        return redirect()->guest(route('login'));
    }

    /**
     * Handles re-sending the verification mail on activities
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse
     */
    public function retryActivate(Request $request, Activity $activity): RedirectResponse
    {
        // Get user
        $user = $request->user();

        // Check if logged in
        if (!$user) {
            flash('Je bent niet ingelogd, log eerst in.', 'warning');
            return redirect()->toRoute('activity.show', compact('activity'));
        }

        // Sanity and hinting
        \assert($user instanceof User);

        // Check if verified
        if ($user->hasVerifiedEmail()) {
            flash('Je hebt het e-mailadres van je account al geverifiëerd.', 'info');
            return redirect()->toRoute('activity.show', compact('activity'));
        }

        // Send mail
        $user->sendEmailVerificationNotification();

        // Notice user
        flash('Er is opnieuw een mailtje gestuurd om je e-mailadres mee te bevestigen.', 'success');

        // Back we go
        return redirect()->toRoute('activity.show', compact('activity'));
    }
}
