<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Forms\NewMemberForm;
use App\Mail\Join\BoardJoinMail;
use App\Mail\Join\UserJoinMail;
use App\Models\JoinSubmission;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
     * Shows the registration form
     * @param Request $request
     * @return Response
     */
    public function index()
    {
        // Create form
        $form = $this->formBuilder->create(NewMemberForm::class, [
            'method' => 'POST',
            'url' => route('join.submit')
        ]);

        // Get content page, if any
        $page = Page::whereSlug('word-lid')->first();

        // Show form
        return view('join.form')->with(compact('form', 'page'));
    }

    /**
     * Handle registration
     * @param SignUpRequest $request
     * @return Response
     */
    public function submit()
    {
        // Get form
        $form = $this->formBuilder->create(NewMemberForm::class);

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

            'windesheim_student' => $userValues['is-student'],
            'newsletter' => $userValues['is-newsletter']
        ]);

        // Validate the submission was created
        if (!$submission->exists()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors('Er is iets fout gegaan bij het aanmelden.');
        }

        // Send mail to user
        Mail::to([
            $submission->only('name', 'email')
        ])->send(new UserJoinMail($submission));

        // Send mail to board
        Mail::to(self::TO_BOARD)
            ->send(new BoardJoinMail($submission));

        // Write session
        Session::put(self::SESSION_NAME, $submission);

        // Send redirect reploy
        return redirect()
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

        // Return join-complete view
        return view('join.complete', compact('submission'));
    }
}
