<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUserLink;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    /**
     * Force auth.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the information page about the Telegram account.
     */
    public function show(Request $request): HttpResponse
    {
        $user = $request->user();
        if ($connected = (bool) $user->telegram_id) {
            $telegramName = BotUserLink::getName('telegram', $user->telegram_id);
        } else {
            $this->alterCspPolicy()
                ->addDirective('connect-src', 'https://telegram.org/')
                ->addDirective('script-src', 'https://telegram.org/')
                ->addDirective('default-src', 'https://oauth.telegram.org/');
        }

        $botUsername = Config::get('telegram.bots.gumbot.username');

        return Response::view('account.telegram.show', [
            'user' => $request->user(),
            'connected' => $connected,
            'telegramBotUsername' => $botUsername,
            'telegramName' => $telegramName ?? null,
            'telegramLink' => "https://t.me/{$botUsername}?start=login",
        ]);
    }

    /**
     * Connects a Telegram account to a user, often called from the Telegram
     * Login API.
     *
     * @link https://core.telegram.org/widgets/login#receiving-authorization-data
     */
    public function create(Request $request): HttpResponse|RedirectResponse
    {
        // Get telegram ID
        $telegramId = $this->getTelegramId($request);

        // Fail if no ID
        if (! $telegramId) {
            flash()->error('Dit lijkt geen geldige koppel-aanvraag, of de aanvraag is verlopen');

            return Response::redirectToRoute('account.tg.show');
        }

        // Get username
        $username = BotUserLink::getName('telegram', $telegramId);

        // Render the view
        return Response::view('account.telegram.create', [
            'telegramName' => $username,
            'telegramId' => $telegramId,
        ]);
    }

    /**
     * Save the new connection.
     */
    public function store(Request $request): RedirectResponse
    {
        // Get telegram ID
        $telegramId = $this->getTelegramId($request);

        // Fail if no ID
        if (! $telegramId) {
            flash()->error('Dit lijkt geen geldige koppel-aanvraag, of de aanvraag is verlopen');

            return Response::redirectToRoute('account.tg.show');
        }

        // Connect
        /** @var User $user */
        $user = $request->user();
        $user->telegram_id = $telegramId;
        $user->save();

        // Get username
        $username = BotUserLink::getName('telegram', $telegramId);

        // Forward
        flash()->success("Je account is nu gekoppeld aan de Telegram account \"{$username}\"");

        return Response::redirectToRoute('account.tg.show');
    }

    /**
     * Submit a deletion request.
     */
    public function delete(Request $request): RedirectResponse
    {
        // Just always delete it
        /** @var User $user */
        $user = $request->user();
        $user->telegram_id = null;
        $user->save(['telegram_id']);

        // Forward
        flash()->success('Je account is niet meer aan een Telegram account gekoppeld', 'success');

        return Response::redirectToRoute('account.index');
    }

    /**
     * Validates that the request came from Telegram.
     *
     * @throws HttpException if the authentication failed, for whatever reason
     */
    private function validateSignature(Request $request): void
    {
        // Get hash
        $hash = $request->get('hash');

        // Get the data used to sign the request
        $dataList = Collection::make($request->except('hash'))
            ->map(static fn ($val, $key) => "{$key}={$val}")
            ->sort()
            ->implode("\n");

        // Validate hash and list
        abort_if(empty($hash) || empty($dataList), HttpResponse::HTTP_BAD_REQUEST, 'Request missing parameters');

        // Get token
        $botToken = Telegram::bot()?->getAccessToken();

        // Fail if no token is available
        abort_unless($botToken, HttpResponse::HTTP_SERVICE_UNAVAILABLE, 'Telegram bot token not available');

        // Compute expected content hash
        $botSecret = hash('sha256', $botToken, true);
        $signed = hash_hmac('sha256', $dataList, $botSecret);

        // Compare using a timing-safe function
        abort_unless(hash_equals($signed, $hash), HttpResponse::HTTP_BAD_REQUEST, 'Request signature is invalid');

        // Validate expiration
        $date = $request->get('auth_date');
        abort_if(time() - $date > 60 * 60, HttpResponse::HTTP_BAD_REQUEST, 'The data signature is no longer valid');
    }

    /**
     * Returns the Telegram ID retrieved from the request (if it's valid) or from
     * the cache if that's still valid. Will only validate Telegram responses if
     * $request is a GET-request.
     *
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
                $username = "#{$telegramId}";
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
