<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BotUserLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Telegram\Bot\BotsManager;

class TelegramController extends Controller
{
    public BotsManager $manager;

    /**
     * All routes are user-only, and we need the Telegram manager
     * @param BotsManager $manager
     */
    public function __construct(BotsManager $manager)
    {
        // Get auth
        $this->middleware('auth');

        // Get manager
        $this->manager = $manager;
    }

    public function index(Request $request)
    {
        # code...
    }

    public function create(Request $request)
    {
        // Get telegram ID
        $telegramId = $this->getTelegramId($request);

        // Fail if no ID
        if (!$telegramId) {
            \flash('Dit lijkt geen geldige koppel-aanvraag, of de aanvraag is verlopen', 'error');
            return Response::redirectToRoute('account.tg');
        }

        // Get username
        $username = $this->getTelegramName($telegramId);

        // Render the view
        return Response::view('account.tg.link', [
            'telegramUser' => $username,
            'telegramId' => $telegramId
        ])->setPrivate()->setMaxAge(30);
    }

    public function store(Request $request)
    {
        // Get telegram ID
        $telegramId = $this->getTelegramId($request);

        // Fail if no ID
        if (!$telegramId) {
            \flash('Dit lijkt geen geldige koppel-aanvraag, of de aanvraag is verlopen', 'error');
            return Response::redirectToRoute('account.tg');
        }

        // Connect
        $user = $request->user();
        \assert($user instanceof User);
        $user->telegram_id = $telegramId;
        $user->save();

        // Get username
        $username = $this->getTelegramName($telegramId);

        // Forward
        \flash("Je account is nu gekoppeld aan de Telegram account [{$username}]", 'success');
        return Response::redirectToRoute('account.tg');
    }

    /**
     * Submit a deletion request
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
        return Response::redirectToRoute('account.tg');
    }

    /**
     * Validates that the request came from Telegram
     * @param Request $request
     * @return void
     * @throws BadRequestHttpException
     */
    private function validateSignature(Request $request): void
    {
        // Get hash
        $hash = $request->get('hash');

        // Get the data used to sign the request
        $dataList = Collection::make($request->except('hash'))
            ->filter(static fn ($val, $key) => "$key=$val")
            ->sort()
            ->implode("\n");

        // Validate hash and list
        if (empty($hash) || empty($dataList)) {
            throw new BadRequestHttpException('Request missing parameters');
        }

        // Get token
        $botToken = $this->manager->bot()->getAccessToken();

        // Fail if no token is available
        if (!$botToken) {
            throw new ServiceUnavailableHttpException('Validation system is not available');
        }

        // Hash the bot token
        $botSecret = hash('sha256', $botToken, true);

        // Get hash of the data
        $signed = hash_hmac('sha256', $dataList, $botSecret);

        // Get hash
        if (!hash_equals($signed, $hash)) {
            throw new BadRequestHttpException('The data signature is invalid');
        }

        // Validate expiration
        $date = $request->get('auth_date');
        if ((time() - $date) > (60 * 60)) {
            throw new BadRequestHttpException('The data signature is no longer valid');
        }
    }

    /**
     * Returns the name of the user on Telegram. Cached for a bit
     * @param string $telegramId
     * @return string
     */
    private function getTelegramName(string $telegramId): ?string
    {
        // Compute key
        $cacheKey = sprintf('tg.usernames.%s', $telegramId);

        // Check cache, can also be null
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
    }

    private function getTelegramId(Request $request): ?string
    {
    // Get session
        $session = $request->session();

    // Check if the hash is set
        if ($request->isMethod('POST') && $request->get('hash')) {
            // Validate the Telegram signature
            $this->validateSignature($request);

            // Get Telegram ID
            $telegramId = $request->get('id');

            // Store it in the session
            $session->put('telegram.id', $telegramId);
            $session->put('telegram.expire', Date::now()->addHour());

            // Store the name in the Cache
            $username = $request->get('username');
            if (empty($username)) {
                $username = trim(sprintf('%s %s', $request->get('first_name'), $request->get('last_name')));
            }
            if (empty($username)) {
                $username = "#${telegramId}";
            }
            BotUserLink::setName('telegram', $telegramId, $username);

            return $telegramId;
        }

    // Allow if set and not expired
        if ($session->has('telegram.id') && $session->get('telegram.expire') > Date::now()) {
            return $session->has('telegram.id');
        }

    // Fail
        return null;
    }
}
