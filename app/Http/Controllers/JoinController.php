<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
    public function submit(Request $request)
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
        if ($introActivity && $introActivity->enrollment_open && !empty($userValues['join-intro'])) {
            // Get redirect
            $redirect = $this->getIntroRedirect($userValues, $request, $introActivity);

            // If we have a redirect, show the 'you're enrolled' message
            if ($redirect) {
                \flash("Bedankt voor je aanmelding, deze is doorgestuurd naar het bestuur.", "success");
                return $redirect;
            }
        }

        // Send redirect reply
        return \redirect()
            ->route('join.complete')
            ->with('submission', $submission);
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
     * Returns a redirect for the data supplied
     * @param array $data
     * @param Request $request
     * @param Activity $activity
     * @return null|RedirectResponse
     */
    private function getIntroRedirect(array $data, Request $request, Activity $activity): ?RedirectResponse
    {
        // Get URL data
        $activityUrl = \route('activity.show', compact('activity'));

        // Find a user
        if ($request->user()) {
            return redirect()->to($activityUrl);
        }

        // Find or create the user
        $user = User::firstOrCreate([
            'email' => $data['email']
        ], [
            'first_name' => $data['first-name'],
            'insert' => $data['insert'],
            'last_name' => $data['last-name'],
            'password' => Hash::make((string) Str::uuid())
        ]);

        // Redirect to login page if account exists
        if (!$user->wasRecentlyCreated) {
            // Set next URL
            \redirect()->setIntendedUrl($activityUrl);

            // Flash message
            \flash('Je hebt al een account op de site, dus om je in te schrijven voor de intro moet je even inloggen.');

            // Redirect to login
            return \redirect()->route('login');
        }

        // Dispatch new account event
        event(new Registered($user));

        // Send a password reset
        Password::broker()->sendResetLink([
            'email' => $user->email
        ]);

        // Log in user
        Auth::guard()->login($user);

        // Create enrollment
        $enrollment = Enrollment::enrollUser($user, $activity);

        // Created OK, perform rest of the show
        if ($enrollment->exists()) {
            return \redirect()->route('enroll.show', compact('activity'));
        }

        // Forward to activity detail page
        return \redirect()->to($activityUrl);
    }
}
