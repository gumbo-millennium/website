<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUserLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    /**
     * Force auth
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Connects a Telegram account to a user, often called from the Telegram
     * Login API
     *
     * @param Request $request
     * @return RedirectResponse|HttpResponse
     * @throws BadRequestHttpException
     * @link https://core.telegram.org/widgets/login#receiving-authorization-data
     */
    public function create(Request $request)
    {
        // Get telegram ID
        $telegramId = $this->getTelegramId($request);

        // Fail if no ID
        if (!$telegramId) {
            \flash('Dit lijkt geen geldige koppel-aanvraag, of de aanvraag is verlopen', 'error');
            return Response::redirectToRoute('account.index');
        }

        // Get username
        $username = BotUserLink::getName('telegram', $telegramId);

        // Render the view
        return Response::view('account.telegram-connect', [
            'telegramName' => $username,
            'telegramId' => $telegramId,
        ])->setPrivate()->setMaxAge(30);
    }

    /**
     * Save the new connection
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BadRequestHttpException
     */
    public function store(Request $request)
    {
        // Get telegram ID
        $telegramId = $this->getTelegramId($request);

        // Fail if no ID
        if (!$telegramId) {
            \flash('Dit lijkt geen geldige koppel-aanvraag, of de aanvraag is verlopen', 'error');
            return Response::redirectToRoute('account.index');
        }

        // Connect
        $user = $request->user();
        \assert($user instanceof User);
        $user->telegram_id = $telegramId;
        $user->save();

        // Get username
        $username = BotUserLink::getName('telegram', $telegramId);

        // Forward
        \flash("Je account is nu gekoppeld aan de Telegram account \"$username\"", 'success');
        return Response::redirectToRoute('account.index');
    }

    /**
     * Submit a deletion request
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        // Just always delete it
        $user = $request->user();
        \assert($user instanceof User);
        $user->telegram_id = null;
        $user->save();

        // Forward
        \flash('Je account is niet meer aan een Telegram account gekoppeld', 'success');
        return Response::redirectToRoute('account.index');
    }

    /**
     * Validates that the request came from Telegram
     *
     * @param Request $request
     * @return void
     * @throws BadRequestHttpException if the request is invalid
     * @throws ServiceUnavailableHttpException if the app's config is invalid
     */
    private function validateSignature(Request $request): void
    {
        // Get hash
        $hash = $request->get('hash');

        // Get the data used to sign the request
        $dataList = Collection::make($request->except('hash'))
            ->map(static fn ($val, $key) => "$key=$val")
            ->sort()
            ->implode("\n");

        // Validate hash and list
        if (empty($hash) || empty($dataList)) {
            throw new BadRequestHttpException('Request missing parameters');
        }

        // Get token
        $botToken = optional(Telegram::bot())->getAccessToken();

        // Fail if no token is available
        if (!$botToken) {
            throw new ServiceUnavailableHttpException('Validation system is not available');
        }

        // Compute expected content hash
        $botSecret = hash('sha256', $botToken, true);
        $signed = hash_hmac('sha256', $dataList, $botSecret);

        // Compare using a timing-safe function
        if (!hash_equals($signed, $hash)) {
            throw new BadRequestHttpException('The data signature is invalid');
        }

        // Validate expiration
        $date = $request->get('auth_date');
        if (time() - $date > 60 * 60) {
            throw new BadRequestHttpException('The data signature is no longer valid');
        }
    }

    /**
     * Returns the Telegram ID retrieved from the request (if it's valid) or from
     * the cache if that's still valid. Will only validate Telegram responses if
     * $request is a GET-request
     *
     * @param Request $request
     * @return string|null
     * @throws RuntimeException if key val
     * @throws BadRequestHttpException
     */
    private function getTelegramId(Request $request): ?string
    {
        // Get session
        $session = $request->session();

        // Check if the hash is set, but only when GET
        if ($request->isMethod('GET') && $request->get('hash')) {
            // Validate the Telegram signature
            $this->validateSignature($request);

            // Get Telegram ID
            $telegramId = $request->get('id');

            // Store it in the session
            $session->put('telegram.id', $telegramId);
            $session->put('telegram.expire', Date::now()->addHour());

            // Store the name in the database
            $username = trim("{$request->get('first_name')} {$request->get('last_name')}");
            if (empty($username)) {
                $username = $request->get('username');
            }
            if (empty($username)) {
                $username = "#${telegramId}";
            }
            BotUserLink::setName('telegram', $telegramId, $username);

            // Return the ID
            return $telegramId;
        }

        // Allow if set and not expired
        if ($session->has('telegram.id') && $session->get('telegram.expire') > Date::now()) {
            return $session->get('telegram.id');
        }

        // Fail
        return null;
    }
}
