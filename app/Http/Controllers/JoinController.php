<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinRequest as JoinWebRequest;
use App\JoinRequest;
use App\Mail\NewAccountMail;
use App\Page;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
     * Returns if users is allowed to use this form.
     *
     * @param Request $request
     * @return bool
     */
    public function canJoin(Request $request) : bool
    {
        $user = $request->user();
        return !($user && $user->hasRole('member'));
    }

    /**
     * Shows the sign-up form
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        if (!$this->canJoin()) {
            return redirect()->route('user.home')->with([
                'status' => 'Je bent al lid van Gumbo Millennium'
            ]);
        }

        // Show form
        return view('join')->with([
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
    public function submit(JoinWebRequest $request)
    {
        if (!$this->canJoin()) {
            return redirect()->route('user.home')->with([
                'status' => 'Je bent al lid van Gumbo Millennium'
            ]);
        }

        // Find the current user
        $user = auth()->user();

        // If no user was found, create a new one with the given e-mail.
        // The JoinWebRequest has checks to ensure the e-mail address is not
        // yet registered.
        if (!$user) {
            $user = $this->createUser($request);

            if (!$user) {
                // Log error
                logger()->warn('Failed to create user-account for {request}!', [
                    'request' => $request->safe()
                ]);

                // Redirect user back
                return back()->with([
                    'status' => 'We could not create your account :('
                ]);
            }

            // Log in the user
            $this->login($user);
        }

        // Redirect to user home if only registering
        if ($request->has('register_only') && !$user) {
            return redirect()->route('user.home');
        }

        // If creation failed, report back
        if (!$joinRequest) {
            return redirect()->back()->with([
                'error' => 'Failed to send request'
            ]);
        }

        // Forward the user
        return redirect()->route('user.join-requests')->with([
            'created' => $joinRequest->id
        ]);
    }

    /**
     * Logs in the user
     *
     * @param User $user
     * @return void
     */
    protected function login(User $user) : void
    {
        // Trigger registered event
        event(new Registered($user));

        // Auto-login
        Auth::guard()->login($user);
    }

    /**
     * Creates a new user from the request
     *
     * @param JoinRequest $request
     * @return User|null
     */
    protected function createUser(JoinRequest $request) : ?User
    {
        if ($request->has('password') && !empty($request->get('password'))) {
            $shouldMail = false;
            $password = $request->get('password');
        } else {
            $shouldMail = true;
            $password = str_random(random_int(10, 14));
        }

        // Create user
        $user = User::create([
            'email' => $request->email,
            'first_name' => $request->first_name,
            'insert' => $request->insert,
            'last_name' => $request->last_name,
            'password' => Hash::make($password),
        ]);

        // Give user 'guest' role
        $user->assignRole('guest');

        // Send e-mail
        Mail::to($user)->queue(
            new NewAccountMail($user, $generated ? $password : null)
        );

        // Return user
        return $user;
    }

    /**
     * Creates the request to join
     *
     * @param JoinRequest $request
     * @return JoinRequestModel
     */
    protected function createJoinRequest(JoinRequest $request, User $user) : JoinRequestModel
    {
        // Create registration request
        $joinRequest = JoinRequest::makeAndSend($user, [
            // Address
            'street' => $request->get('street'),
            'number' => $request->get('number'),
            'zipcode' => $request->get('zipcode'),
            'city' => $request->get('city'),
            'country' => $request->get('country'),

            // Contact info
            'phone' => $request->get('phone'),
            'date-of-birth' => $request->get('date-of-birth'),

            // Accepts policies
            'accept-policy' => $request->get('accept-policy'),
            'accept-newsletter' => $request->get('newsletter'),
        ]);

        // Check if created
        if (!$joinRequest) {
            logger()->critical('Join membership system failed for {user}, using {request}.', [
                'user' => $user,
                'request' => $request->safe()
            ]);
        }
    }
}
