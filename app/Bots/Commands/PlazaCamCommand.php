<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Http\Controllers\PlazaCamController;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Actions;
use Telegram\Bot\FileUpload\InputFile;

class PlazaCamCommand extends Command
{
    private const REPLY_GUEST = <<<'MSG'
    🔒 De %s is alleen toegankelijk voor leden.

    Log in via /login.
    MSG;

    private const REPLY_EXPIRED = <<<'MSG'
    🕸 Deze foto is te stoffig...

    De %s is te ver verouderd om nog nuttig te zijn,
    en kan daarom niet meer worden opgevraagd.
    MSG;

    private const COMMAND_IMAGE_MAP = [
        'coffeecam' => 'coffee',
        'koffiecam' => 'coffee',
        'plazacam' => 'plaza',
    ];

    private const NAME_LABEL_MAP = [
        'coffee' => 'Koffiecam',
        'plaza' => 'Plazacam',
    ];

    /**
     * The name of the Telegram command.
     *
     * @var string
     */
    protected $name = 'plazacam';

    /**
     * Command Aliases - Helpful when you want to trigger command with more than one name.
     *
     * @var array<string>
     */
    protected $aliases = [
        'koffiecam',
        'coffeecam',
    ];

    /**
     * The Telegram command description.
     *
     * @var string
     */
    protected $description = 'Toont de plaza of koffiecam';

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Get image name
        $image = self::COMMAND_IMAGE_MAP[$this->getName()] ?? 'plazacam';
        $imageName = self::NAME_LABEL_MAP[$image];

        // Get user
        $user = $this->getUser();

        // Reject if rate-limited
        if (! $user) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_GUEST, $imageName),
            ]);

            return;
        }

        // Check if expired
        if (PlazaCamController::isExpired($image)) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_EXPIRED, $imageName),
            ]);

            return;
        }

        // Send upload status
        $this->replyWithChatAction(['action' => Actions::UPLOAD_PHOTO]);

        // Get file
        $file = PlazaCamController::getPlazacamPath($image);
        $steam = Storage::readStream($file);

        // Prep file
        $file = new InputFile($steam, strtolower("${image}.jpg"));

        // Return message
        $this->replyWithPhoto([
            'photo' => $file,
            'caption' => $imageName,
        ]);
    }
}
