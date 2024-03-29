<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\Enroll;
use App\Forms\NewMemberForm;
use App\Helpers\Str;
use App\Mail\Join\BoardJoinMail;
use App\Mail\Join\UserJoinMail;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\JoinSubmission;
use App\Models\Page;
use App\Models\Ticket;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Kris\LaravelFormBuilder\FormBuilder;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Handles sign ups to the student community. Presents a whole form and
 * isn't very user-friendly.
 */
class JoinController extends Controller
{
    private const SESSION_NAME = 'join.submission';

    /**
     * E-mail address and name of the board.
     */
    private const TO_BOARD = [[
        'name' => 'Bestuur Gumbo Millennium',
        'email' => 'bestuur@gumbo-millennium.nl',
    ]];

    /**
     * A useful form builder.
     */
    private FormBuilder $formBuilder;

    /**
     * The next upcoming introduction activity.
     */
    private ?Activity $activity;

    /**
     * The cheapest available ticket for the activity, if any.
     */
    private ?Ticket $activityTicket;

    /**
     * Creates a new controller with form builder.
     */
    public function __construct(FormBuilder $builder)
    {
        // Form builder
        $this->formBuilder = $builder;

        // Attach middleware to load the intro ticket and activity when
        // this route is requested
        $this->middleware(function (Request $request, Closure $next) {
            $this->determineActivityAndTicket();

            return $next($request);
        });
    }

    /**
     * Shows the registration form.
     */
    public function index(SessionStore $session, Router $router): SymfonyResponse
    {
        // Check for an intro
        $introActivity = $this->activity;
        $introTicket = $this->activityTicket;

        // Check if the URL is an intro route
        $isIntro = Str::endsWith($router->currentRouteName(), 'intro');

        // Create form
        $form = $this->formBuilder->create(NewMemberForm::class, [
            'method' => 'POST',
            'url' => route('join.submit'),
            'intro-checked' => $isIntro,
            'intro-activity' => $introActivity,
            'intro-ticket' => $introTicket,
        ]);

        // Get content data
        $page = Page::whereSlug('word-lid')->first();
        $pageTemplate = 'join.form';

        // Return a message if there's no intro right now
        if ($isIntro && (
            $introActivity === null || $introTicket === null
            || $introActivity->available_seats === 0
            || $introActivity->enrollment_open === false
        )) {
            return Response::view('join.no-intro', [
                'activity' => $introActivity,
                'ticket' => $introTicket,
            ]);
        }

        // Flag to auto-enroll to the introduction week
        $session->put('intro.auto-intro', $isIntro);

        // Override if we're force joining the introduction
        if ($isIntro) {
            $page = Page::whereSlug('word-lid-intro')->first() ?? $page;
            $pageTemplate = 'join.form-intro';
        }

        // Show form
        return Response::view($pageTemplate, [
            'form' => $form,
            'page' => $page,
            'isIntro' => $isIntro,
            'activity' => $introActivity,
            'ticket' => $introTicket,
        ]);
    }

    /**
     * Handle the submission of the form, either for the intro-version or for the
     * regular version.
     */
    public function submit(Request $request): SymfonyResponse
    {
        // Get intro activity
        $introActivity = $this->activity;
        $introTicket = $this->activityTicket;

        // Get form
        $form = $this->formBuilder->create(NewMemberForm::class, [
            'intro-activity' => $introActivity,
            'intro-ticket' => $introTicket,
        ]);

        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        // Get values
        $userValues = $form->getFieldValues();

        // Format user birthday
        $birthday = Date::createFromFormat('Y-m-d', $userValues['date-of-birth']);

        // Get name and e-mail address
        $submission = JoinSubmission::create([
            'first_name' => $userValues['first-name'],
            'insert' => $userValues['insert'],
            'last_name' => $userValues['last-name'],

            'email' => $userValues['email'],
            'phone' => $userValues['phone'],

            'date_of_birth' => $birthday->format('Y-m-d'),
            'gender' => $userValues['gender'],

            'street' => $userValues['street'],
            'number' => $userValues['number'],
            'postal_code' => $userValues['postal-code'],
            'city' => $userValues['city'],
            'country' => $userValues['country'],

            'windesheim_student' => $userValues['is-student'] ? '1' : '0',
            'newsletter' => $userValues['is-newsletter'] ? '1' : '0',

            'referrer' => $userValues['referrer'],
        ]);

        // Validate the submission was created
        if (! $submission->exists()) {
            return Redirect::back()
                ->withInput()
                ->withErrors('Er is iets fout gegaan bij het aanmelden.');
        }

        // Send mail to submitter
        $recipient = $submission->only('name', 'email');
        Mail::to([$recipient])->queue(new UserJoinMail($submission));

        // Send mail to board
        Mail::to(self::TO_BOARD)->queue(new BoardJoinMail($submission));

        // Write session
        Session::put(self::SESSION_NAME, $submission);

        // Check if the user wants to join the introduction
        if (! $introActivity || empty($userValues['join-intro'])) {
            // Send redirect reply
            return Redirect::route('join.complete')
                ->with('submission', $submission);
        }

        // Get user
        $user = $request->user();

        if (! $user) {
            $user = $this->createJoinUser($userValues);

            // No user means an existing user was found, request login.
            if (! $user) {
                // Set next URL
                Redirect::setIntendedUrl(
                    URL::route('enroll.show', ['activity' => $introActivity]),
                );

                // Flash message
                flash(<<<'EOL'
                    Je hebt al een account op de site, dus om je in
                    te schrijven voor de intro moet je even inloggen.
                    EOL);

                // Redirect to login
                return Redirect::route('login');
            }

            Auth::login($user);
        }

        // Join intro
        $enrollment = $this->joinIntroActivity($introActivity, $introTicket, $user);

        // No enrollment means enrolling was blocked
        if (! $enrollment) {
            // Flash failure
            flash(
                'Je aanmelding is ontvangen, maar je kon helaas niet ingeschreven worden op de introductieweek.',
                'warning',
            );

            // Redirect to welcome

            return Redirect::route('join.complete')
                ->with('submission', $submission);
        }

        // Flash OK
        flash('Bedankt voor je aanmelding, deze is doorgestuurd naar het bestuur.', 'success');

        // Redirect to proper location
        return Redirect::route('enroll.show', ['activity' => $introActivity]);
    }

    /**
     * Show the 'thank you for joining' page.
     */
    public function complete(): SymfonyResponse
    {
        // Redirect to form if they're reloading the page
        // and the submission was removed
        if (! Session::has(self::SESSION_NAME)) {
            return Redirect::route('join.form');
        }

        // Get submission from session
        $submission = Session::get(self::SESSION_NAME);

        // Return join-complete view
        return Response::view('join.complete', [
            'submission' => $submission,
            'activity' => $this->activity,
        ]);
    }

    /**
     * Determine the activity and the ticket to use when determining if there's
     * an introduction period to be enrolled into.
     */
    private function determineActivityAndTicket(): void
    {
        // Get the activity
        $this->activity = Activity::query()
            // Should start with intro-
            ->where('slug', 'like', 'intro-%')
            // Should be available to anons
            ->whereAvailable(new User())
            // Should be in the future
            ->whereInTheFuture()
            // Load tickets too
            ->with(['tickets'])
            ->first();

        // Get the ticket
        $this->activityTicket = $this->activity?->tickets
            ->filter(fn (Ticket $ticket) => $ticket->is_being_sold && $ticket->is_public && $ticket->quantity_available !== 0)
            ->sortBy('total_price')
            ->first();
    }

    /**
     * Finds or creates a user that matches the data in this enrollment.
     */
    private function createJoinUser(array $data): ?User
    {
        // Find or create the user
        $user = User::firstOrCreate([
            'email' => $data['email'],
        ], [
            'first_name' => $data['first-name'],
            'insert' => $data['insert'],
            'last_name' => $data['last-name'],
            'password' => '!',
        ]);

        // Return null if the user already exists
        if (! $user->wasRecentlyCreated) {
            return null;
        }

        // Send a password reset
        Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        return $user;
    }

    /**
     * Returns a redirect for the data supplied.
     *
     * @return null|RedirectResponse
     */
    private function joinIntroActivity(
        Activity $activity,
        Ticket $ticket,
        User $user
    ): ?Enrollment {
        // Check if the user can actually enroll
        if (! Enroll::canEnroll($activity)) {
            Log::info('User {user} tried to enroll into {actiity}, but it\'s not allowed', [
                'user' => $user,
                'activity' => $activity,
            ]);

            // Return existing enrollment or null
            return Enrollment::findActive($user, $activity);
        }

        // Create the enrollment
        $enrollment = Enroll::createEnrollment($activity, $ticket);

        // Return enrollment
        return $enrollment->exists ? $enrollment : null;
    }
}
