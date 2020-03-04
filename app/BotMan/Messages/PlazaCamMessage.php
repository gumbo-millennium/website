<?php

declare(strict_types=1);

namespace App\BotMan\Messages;

use App\Models\User;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\Drivers\Telegram\TelegramDriver;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use UnderflowException;

class PlazaCamMessage extends AbstractMessage
{
    private const COMMAND_IMAGE_MAP = [
        '/coffeecam' => 'coffee',
        '/koffiecam' => 'coffee',
        '/plazacam' => 'plaza',
    ];

    private const NAME_LABEL_MAP = [
        'coffee' => 'Koffiecam',
        'plaza' => 'Plazacam',
    ];

    /**
     * Sends the requested cam to the user
     * @param BotMan $bot
     * @param null|User $user
     * @return void
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     * @throws RouteNotFoundException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function run(BotMan $bot, ?User $user): void
    {
        // if (!$user || !$user->hasPermissionTo('plazacam-view')) {
        //     $bot->randomReply([
        //         'Sorry, deze cam is niet voor jou beschikbaar',
        //         'Oeps, het lijkt er op dat je geen toegang hebt tot de plazacam',
        //         '"Computer says no"',
        //         'Ai, het lijkt er op dat je deze cam niet mag opvragen.'
        //     ]);
        //     return;
        // }

        // Send image notification
        if ($bot->getDriver() instanceof TelegramDriver) {
            $bot->sendRequest('sendChatAction', ['action' => 'upload_photo']);
        } else {
            $bot->types();
        }

        // Get cam
        $message = $bot->getMessage();

        // Log
        logger()->info('Recieved proper Plazacam request {message}.', compact('message'));

        // Get cam name
        $name = (string) (self::COMMAND_IMAGE_MAP[trim($message->getText())] ?? null);

        // HACK remove when we're authenticating
        $user ??= User::query()->permission(['plazacam-update'])->first(['id', 'name']);
        if (!$user) {
            $bot->reply('Sorry, ik heb moenteel geen plazacam voor je');
            return;
        }

        // Get URL
        $camUrl = URL::temporarySignedRoute('api.plazacam.view', now()->addMinute(), [
            'image' => $name,
            'user' => $user->id
        ]);

        try {
            // Get image
            $attachment = new Image($camUrl, [
                'custom_payload' => true
            ]);

            // Prep payload
            $payload = OutgoingMessage::create(self::NAME_LABEL_MAP[$name])
                ->withAttachment($attachment);

            // Log it
            logger()->debug('Sending plazacam', [
                'name' => $name,
                'user' => $user,
                'url' => $camUrl,
                'attachment' => $attachment,
                'payload' => $payload
            ]);

            // Send payload
            $bot->reply($payload);
        } catch (InvalidArgumentException $exception) {
            report($exception);
            $bot->reply('Sorry, deze webcam ken ik niet');
        } catch (UnderflowException $exception) {
            report($exception);
            $bot->reply('Sorry, deze webcam is tijdelijk niet beschikbaar');
        }
    }
}
