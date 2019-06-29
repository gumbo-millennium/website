<?php

namespace App\Http\Controllers\Join;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShortJoinRequest;
use App\Mail\Join\BoardJoinMail;
use App\Mail\Join\SimpleJoinMail;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

/**
 * Short sign-up form
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class ShortController extends Controller
{
    use BuildsJoinSubmissions;

    /**
     * E-mail address and name of the board
     */
    const TO_BOARD = [[
        'name' => 'Bestuur Gumbo Millennium',
        'email' => 'bestuur@gumbo-millennium.nl'
    ]];

    /**
     * Shows the sign-up form
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Show form
        return view('main.join.index')->with([
            'page' => Page::slug('join')->first(),
            'user' => $request->user()
        ]);
    }

    /**
     * Handldes the user registration
     *
     * @param SignUpRequest $request
     * @return Response
     */
    public function submit(ShortJoinRequest $request)
    {
        // Get name and e-mail address
        $name = $request->get('name');
        $email = $request->get('email');

        // Sends the e-mails
        $submission = $this->buildJoinSubmission($request);

        if (!$submission) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors('Er is iets fout gegaan bij het aanmelden.');
        }

        // Send mail to user
        Mail::to([compact('name', 'email')])
            ->send(new SimpleJoinMail($submission));

        // Send mail to board
        Mail::to(self::TO_BOARD)
            ->send(new BoardJoinMail($submission));

        // Send redirect reploy
        return redirect()
            ->route('join.complete')
            ->with('submission', $submission);
    }

    /**
     * Show verification page
     *
     * @param Request $request
     * @return Response
     */
    public function complete(Request $request)
    {
        $thanksPage = Page::slug('join-complete')->first();
        $viewName = $thanksPage ? 'main.wordpress.page' : 'main.join.complete';

        return view($viewName, [
            'page' => $thanksPage,
            'name' => $request->get('join-name') ?? null,
            'email' => $request->get('join-email') ?? null
        ]);
    }
}
