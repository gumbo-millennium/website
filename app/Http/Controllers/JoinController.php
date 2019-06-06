<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinRequest;
use App\Mail\NewAccountMail;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\JoinMail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Mail\JoinBoardMail;

/**
 * Handles user registration and enrollment to the student community. Join
 * requests are very flexible. A new user may choose to join the commnity, or
 * may just merely be registering a new account. This is all indicated using
 * flags and some flags are only available in certain scenarios.
 *
 * These scenarios are:
 *  - guests may: only register, (register and) join
 *  - logged in users may: (register and) join
 *  - members may: -none of the above-
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinController extends Controller
{
    /**
     * E-mail address and name of the board
     */
    const TO_BOARD = [
        'name' => 'Bestuur Gumbo Millennium',
        'email' => 'bestuur@gumbo-millennium.nl',
    ];
    /**
     * Gets the name of the user from the request
     *
     * @param JoinRequest $request
     * @return string
     */
    protected function getName(JoinRequest $request) : string
    {
        return collect($request->only(['first_name', 'insert', 'last_name']))
            ->reject('empty')
            ->implode(' ');
    }
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
    public function submit(JoinRequest $request)
    {
        // Get name and e-mail address
        $name = $this->getName($request);
        $email = $request->get('email');

        // Sends the e-mails
        $this->sendJoinRequest($request, $name, $email);

        // Forward the user
        return redirect()->route('join.complete')
            ->with([
                'join-name' => $name,
                'join-email' => $email
            ]);
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

    /**
     * Creates the request to join
     *
     * @param JoinRequest $request
     * @return void
     */
    protected function sendJoinRequest(JoinRequest $request, string $userName, string $userEmail) : void
    {
        $data = collect([
            // Personal data
            'first_name' => $request->get('first_name'),
            'insert' => $request->get('insert'),
            'last_name' => $request->get('last_name'),

            // E-mail
            'email' => $request->get('email'),

            // Address
            'street' => $request->get('street'),
            'number' => $request->get('number'),
            'zipcode' => mb_strtoupper($request->get('zipcode')),
            'city' => $request->get('city'),
            'country' => $request->get('country'),

            // Contact info
            'phone' => $request->get('phone'),
            'date-of-birth' => $request->get('date-of-birth'),
            'windesheim-student' => $request->get('windesheim-student'),

            // Accepts policies
            'accept-policy' => $request->get('accept-policy'),
            'accept-newsletter' => $request->get('accept-newsletter'),
        ]);

        // Construct a sane board e-mail
        $recipient = [
            'name' => $userName,
            'email' => $userEmail,
        ];

        // Build e-mail objects
        $userMail = new JoinMail($data, $recipient, self::TO_BOARD);
        $boardMail = new JoinBoardMail($data, $recipient, self::TO_BOARD);

        // Send e-mails
        Mail::queue($userMail);
        Mail::queue($boardMail);
    }
}
