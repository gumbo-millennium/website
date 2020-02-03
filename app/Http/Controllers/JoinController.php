<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\JoinRequest;
use App\Mail\Join\BoardJoinMail;
use App\Mail\Join\UserJoinMail;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PDOException;

/**
 * Handles sign ups to the student community. Presents a whole form and
 * isn't very user-friendly.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinController extends Controller
{
    /**
     * E-mail address and name of the board
     */
    private const TO_BOARD = [[
        'name' => 'Bestuur Gumbo Millennium',
        'email' => 'bestuur@gumbo-millennium.nl',
    ]];

    /**
     * Shows the sign-up form
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Show form
        return view('static.join')->with([
            'page' => Page::slug('join')->first(),
            'user' => $request->user()
        ]);
    }

    /**
     * Handldes the user registration
     * @param SignUpRequest $request
     * @return Response
     */
    public function submit(JoinRequest $request)
    {
        // Get name and e-mail address
        $email = $request->get('email');
        $name = collect($request->only(['first_name', 'insert', 'last_name']))
            ->reject('empty')
            ->implode(' ');

        // Get the submission
        $submission = $request->submission();

        try {
            // Try to create it
            $submission->save();
        } catch (PDOException $e) {
            // Log the error, but don't act on it
            logger()->warn('Failed to create join submission [submission].', [
                'exception' => $e,
                'submission' => $submission
            ]);
        }

        // Validate the submission was created
        if (!$submission->exists()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors('Er is iets fout gegaan bij het aanmelden.');
        }

        // Send mail to user
        Mail::to([compact('name', 'email')])
            ->send(new UserJoinMail($submission));

        // Send mail to board
        Mail::to(self::TO_BOARD)
            ->send(new BoardJoinMail($submission));

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
    public function complete(Request $request)
    {
        // Redirect to form if they're reloading the page
        // and the submission was removed
        if (!$request->has('submission')) {
            return redirect()->route('join.index');
        }

        // Return join-complete view
        return view('static.join-complete', [
            'submission' => $request->submission
        ]);
    }
}
