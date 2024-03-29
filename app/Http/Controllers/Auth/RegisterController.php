<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Forms\RegisterForm;
use App\Forms\RegisterPrivacyForm;
use App\Helpers\Str;
use App\Http\Controllers\Auth\Traits\RedirectsToHomepage;
use App\Http\Controllers\Controller;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Kris\LaravelFormBuilder\FormBuilder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Registration controller.
 */
class RegisterController extends Controller
{
    use RedirectsToHomepage;
    use RedirectsUsers;

    private const SESSION_ACCESS = 'onboarding.after-registration';

    private const DATA_SESSION_KEY = 'register.user';

    private const PRIVACY_CACHE_KEY = 'register.privacy.companies';

    private const PRIVACY_COMPANY_FILE = 'assets/yaml/privacy/companies.yaml';

    /**
     * A useful form builder.
     */
    private FormBuilder $formBuilder;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FormBuilder $formBuilder)
    {
        // Form builder
        $this->formBuilder = $formBuilder;

        // Middleware
        $this->middleware('guest')->except('afterRegister');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        // Create form
        $form = $this->formBuilder->create(RegisterForm::class, [
            'method' => 'POST',
            'url' => route('register'),
        ]);

        // Title
        SEOMeta::setTitle('Registreren');
        SEOMeta::setRobots('noindex,nofollow');

        // Make form
        return view('auth.register', compact('form'));
    }

    /**
     * Registers a new user in the system.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Get form
        $form = $this->formBuilder->create(RegisterForm::class);

        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        // Get values
        $userValues = $form->getFieldValues();

        // Format some values
        $userValues['alias'] = empty($userValues['alias']) ? null : Str::lower($userValues['alias']);
        $userValues['email'] = Str::lower($userValues['email']);
        $userValues['password'] = Hash::make($userValues['password']);

        // Store in session
        Session::put(self::DATA_SESSION_KEY, $userValues);

        // Redirect to sign-up page
        return Response::redirectToRoute('register.register-privacy');
    }

    /**
     * Shows the 'what we steal from your privé' message.
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function showPrivacy(Request $request, Repository $cache)
    {
        // Redirect if wrong
        if (! Session::has(self::DATA_SESSION_KEY)) {
            return Response::redirectToRoute('register');
        }

        // Title
        SEOMeta::setTitle('Jouw privacy');
        SEOMeta::setRobots('noindex,nofollow');

        // Create form
        $form = $this->formBuilder->create(RegisterPrivacyForm::class, [
            'method' => 'POST',
            'url' => route('register.register-privacy'),
        ]);

        // Check cache
        $companies = $cache->get(self::PRIVACY_CACHE_KEY);
        if (! $companies) {
            // Get new file
            $path = \resource_path(self::PRIVACY_COMPANY_FILE);

            try {
                // Read file
                $companies = Yaml::parseFile($path);
            } catch (ParseException $exception) {
                // Log
                logger()->error('Failed to parse YAML {path}: {exception}', compact('path', 'exception'));
                // Convert to empty array
                $companies = [];
            }
            $cache->put(self::PRIVACY_CACHE_KEY, $companies, now()->addDay());
        }

        // Show page
        return Response::view('auth.register-privacy', [
            'companies' => $companies,
            'form' => $form,
            'user' => Session::get(self::DATA_SESSION_KEY),
        ]);
    }

    /**
     * Confirms privacy policy and creates account.
     *
     * @return Illuminate\Http\RedirectResponse
     * @throws RuntimeException
     */
    public function savePrivacy()
    {
        // Get form
        $form = $this->formBuilder->create(RegisterPrivacyForm::class);

        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        // Redirect if wrong
        if (! Session::has(self::DATA_SESSION_KEY)) {
            return Response::redirectToRoute('register');
        }

        // Get user request
        $userRequest = Session::pull(self::DATA_SESSION_KEY);

        // Create a user with the values
        $user = User::create($userRequest);

        // Dispatch event
        event(new Registered($user));

        // Log in user
        Auth::guard()->login($user);

        // Flag as valid
        Session::put(self::SESSION_ACCESS, 'true');

        // Forward client
        return response()
            ->redirectToRoute('onboarding.new-account');
    }

    /**
     * Show welcome response.
     *
     * @return Response
     * @throws RuntimeException
     */
    public function afterRegister(Request $request)
    {
        // Check user
        $user = $request->user();

        // User can't be older than 15 mins
        if ($user->created_at < now()->subMinutes(15)) {
            return redirect()->intended();
        }

        // Client may want to leave
        if ($request->has('continue')) {
            return redirect()->intended();
        }

        // Title
        SEOMeta::setTitle('Registratie voltooid');

        // Show onboarding
        return view('onboarding.new-account', [
            'nextUrl' => route('onboarding.new-account', ['continue' => true]),
        ]);
    }
}
