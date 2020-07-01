<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\EnrollmentServiceContract;
use App\Forms\NewMemberForm;
use App\Helpers\Str;
use App\Mail\Join\BoardJoinMail;
use App\Mail\Join\UserJoinMail;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\JoinSubmission;
use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Kris\LaravelFormBuilder\FormBuilder;

/**
 * Handles sign ups to the student community. Presents a whole form and
 * isn't very user-friendly.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinController extends Controller
{
    private const SESSION_NAME = 'join.submission';

    /**
     * E-mail address and name of the board
     */
    private const TO_BOARD = [[
        'name' => 'Bestuur Gumbo Millennium',
        'email' => 'bestuur@gumbo-millennium.nl',
    ]];


    /**
     * A useful form builder
     */
    private FormBuilder $formBuilder;

    /**
     * Creates a new controller with form builder
     * @param FormBuilder $builder
     */
    public function __construct(FormBuilder $builder)
    {
        // Form builder
        $this->formBuilder = $builder;
    }

    /**
     * Returns the introduction week, matching by slug
     */
    private function getIntroActivity(): ?Activity
    {
        // Query
        return Activity::query()
            // Find the activity starting with intro
            ->where('slug', 'LIKE', 'intro-%')

            // Make sure the activity is AT LEAST 2 days long
            ->whereRaw('`end_date` > DATE_ADD(`start_date`, INTERVAL 2 DAY)')

            // Get the one that starts soonest
            ->orderBy('start_date')

            // Return first
            ->first();
    }

    /**
     * Shows the registration form
     * @param SessionStore $session
     * @param Router $router
     * @return Response
     */
    public function index(SessionStore $session, Router $router)
    {
        // Check for an intro
        $introActivity = $this->getIntroActivity();
        $isIntro = Str::endsWith($router->currentRouteName(), 'intro');

        // Create form
        $form = $this->formBuilder->create(NewMemberForm::class, [
            'method' => 'POST',
            'url' => route('join.submit'),
            'intro-checked' => $isIntro,
            'intro-activity' => $introActivity
        ]);

        // Get content data
        $page = Page::whereSlug('word-lid')->first();
        $pageTemplate = 'join.form';

        // Return a message if there's no intro right now
        if ($isIntro && ($introActivity === null || !$introActivity->enrollment_open)) {
            return response()
                ->view('join.no-intro', ['intro' => $introActivity])
                ->setPublic();
        }

        // Flag to auto-enroll to the introduction week
        $session->put('intro.auto-intro', $isIntro);

        // Override if we're force joining the introduction
        if ($isIntro) {
            $page = Page::whereSlug('word-lid-intro')->first() ?? $page;
            $pageTemplate = 'join.form-intro';
        }

        // Show form
        return response()
            ->view($pageTemplate, compact('form', 'page', 'isIntro') + [
                'intro' => $introActivity
            ]);
    }

    /**
     * Handle registration
     * @param SignUpRequest $request
     * @return Response
     */
    public function submit(EnrollmentServiceContract $enrollService, Request $request)
    {
        // Get intro activity
        $introActivity = $this->getIntroActivity();

        // Get form
        $form = $this->formBuilder->create(NewMemberForm::class, [
            'intro-activity' => $introActivity
        ]);

        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        // Get values
        $userValues = $form->getFieldValues();

        // Get name and e-mail address
        $submission = JoinSubmission::create([
            'first_name' => $userValues['first-name'],
            'insert' => $userValues['insert'],
            'last_name' => $userValues['last-name'],

            'email' => $userValues['email'],
            'phone' => $userValues['phone'],

            'date_of_birth' => $userValues['date-of-birth'],
            'gender' => $userValues['gender'],

            'street' => $userValues['street'],
            'number' => $userValues['number'],
            'postal_code' => $userValues['postal-code'],
            'city' => $userValues['city'],
            'country' => $userValues['country'],

            'windesheim_student' => $userValues['is-student'] ? '1' : '0',
            'newsletter' => $userValues['is-newsletter'] ? '1' : '0'
        ]);

        // Validate the submission was created
        if (!$submission->exists()) {
            return redirect()
                ->back()
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
        if (!$introActivity || empty($userValues['join-intro'])) {
            // Send redirect reply
            return \redirect()
                ->route('join.complete')
                ->with('submission', $submission);
        }

        // Get user
        $user = $request->user();

        if (!$user) {
            $user = $this->createJoinUser($userValues);

        // No user means an existing user was found, request login.
            if (!$user) {
                // Set next URL
                \redirect()->setIntendedUrl(
                    \route('activity.show', ['activity' => $introActivity])
                );

                    // Flash message
                    \flash(<<<'EOL'
                    Je hebt al een account op de site, dus om je in
                    te schrijven voor de intro moet je even inloggen.
                    EOL);

                    // Redirect to login
                    return \redirect()->route('login');
            }

            Auth::login($user);
        }

        // Join intro
        $enrollment = $this->joinIntroActivity(
            $enrollService,
            $introActivity,
            $user,
        );

        // No enrollment means enrolling was blocked
        if (!$enrollment) {
            // Flash failure
            \flash(
                'Je aanmelding is ontvangen, maar je kon helaas niet ingeschreven worden op de introductieweek.',
                'warning'
            );

            // Redirect to welcome

            return \redirect()
                ->route('join.complete')
                ->with('submission', $submission);
        }

        // Flash OK
        \flash("Bedankt voor je aanmelding, deze is doorgestuurd naar het bestuur.", "success");

        // Redirect to proper location
        return \response()
            ->redirectToRoute('enroll.show', ['activity' => $introActivity])
            ->setPrivate();
    }

    /**
     * Request completed
     * @param Request $request
     * @return View
     */
    public function complete()
    {
        // Redirect to form if they're reloading the page
        // and the submission was removed
        if (!Session::has(self::SESSION_NAME)) {
            return redirect()->route('join.form');
        }

        // Get submission from session
        $submission = Session::get(self::SESSION_NAME);

        // Get introduction activity
        $introActivity = $this->getIntroActivity();

        // Return join-complete view
        return view('join.complete', compact('submission', 'introActivity'));
    }

    /**
     * Finds or creates a user that matches the data in this enrollment
     * @param array $data
     * @return null|User
     */
    private function createJoinUser(array $data): ?User
    {
        // Find or create the user
        $user = User::firstOrCreate([
            'email' => $data['email']
        ], [
            'first_name' => $data['first-name'],
            'insert' => $data['insert'],
            'last_name' => $data['last-name'],
            'password' => Hash::make((string) Str::uuid())
        ]);

        // Return null if the user already exists
        if (!$user->wasRecentlyCreated) {
            return null;
        }

        // Dispatch new account event
        event(new Registered($user));

        // Send a password reset
        Password::broker()->sendResetLink([
            'email' => $user->email
        ]);

        return $user;
    }

    /**
     * Returns a redirect for the data supplied
     * @param EnrollmentServiceContract $enrollService
     * @param Request $request
     * @param Activity $activity
     * @param array $data
     * @return null|RedirectResponse
     */
    private function joinIntroActivity(
        EnrollmentServiceContract $enrollService,
        Activity $activity,
        User $user
    ): ?Enrollment {
        // Check if we need to lock
        $lock = $enrollService->useLocks() ? $enrollService->getLock($activity) : null;

        // Get enrollment
        $enrollment = null;

        try {
            // Get a lock
            optional($lock)->block(15);

            // Check if the user can actually enroll
            if (!$enrollService->canEnroll($activity, $user)) {
                Log::info('User {user} tried to enroll into {actiity}, but it\'s not allowed', [
                    'user' => $user,
                    'activity' => $activity
                ]);

                // Return existing enrollment or null
                return Enrollment::findActive($user, $activity);
            }

            // Create the enrollment
            $enrollment = $enrollService->createEnrollment($activity, $user);
        } catch (LockTimeoutException $exception) {
            // Report timeout
            \report($exception);

            // Return
            return null;
        } finally {
            // Free lock
            \optional($lock)->release();
        }

        // Advance the enrollment
        $enrollService->advanceEnrollment($activity, $enrollment);

        // Return enrollment
        return $enrollment->exists ? $enrollment : null;
    }
}
