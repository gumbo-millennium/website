<?php

declare(strict_types=1);

namespace App\Bots\Commands;

use App\Helpers\Str;
use App\Models\Webcam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Actions;
use Telegram\Bot\FileUpload\InputFile;

class PlazaCamCommand extends Command
{
    private const REPLY_GUEST = <<<'MSG'
    🔒 Deze camera is alleen toegankelijk voor leden.

    Log in via /login.
    MSG;

    private const REPLY_EXPIRED = <<<'MSG'
    🕸 Deze foto is te stoffig...

    De %s is te ver verouderd om nog nuttig te zijn,
    en kan daarom niet meer worden opgevraagd.
    MSG;

    private const REPLY_NO_SUCH_CAMERA = <<<'MSG'
    🔒 Deze camera is niet beschikbaar.

    De opgevraagde camera kon niet worden gevonden.
    MSG;

    protected ?Collection $cams = null;

    /**
     * The name of the Telegram command.
     *
     * @var string
     */
    protected $name = 'plazacam';

    /**
     * The Telegram command description.
     *
     * @var string
     */
    protected $description = 'Toont de plaza of koffiecam';

    /**
     * Get Command Aliases.
     *
     * Helpful when you want to trigger command with more than one name.
     */
    public function getAliases(): array
    {
        $cameras = $this->getCameras()
            ->where('slug', '!=', $this->getName());

        return Collection::make()
            ->merge($cameras->pluck('slug'))
            ->merge($cameras->pluck('command'))
            ->map(fn ($value) => Str::slug($value))
            ->reject(fn ($value) => $value === $this->getName() || ! Str::endsWith($value, 'cam'))
            ->unique()
            ->toArray();
    }

    public function getDescriptionFor(string $command): string
    {
        $targetCamera = $this->getCameras()
            ->filter(fn ($row) => Str::slug($row->command) === $command || Str::slug($row->slug) === $command)
            ->first();

        return $targetCamera ? "Toont de {$targetCamera->name}" : $this->description;
    }

    /**
     * Handle the activity.
     */
    public function handle()
    {
        // Get user
        $user = $this->getUser();

        // Reject if rate-limited
        if (! $user) {
            $this->replyWithMessage(['text' => self::REPLY_GUEST]);

            return;
        }

        // Get image
        $requested = Str::slug($this->getName() ?? 'plazacam');
        $webcam = Webcam::findBySlug($requested);

        if (! $webcam) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_NO_SUCH_CAMERA, e(strip_tags($requested))),
            ]);

            return;
        }

        // Check if expired
        if ($webcam->is_expired) {
            $this->replyWithMessage([
                'text' => $this->formatText(self::REPLY_EXPIRED, $webcam->name),
            ]);

            return;
        }

        // Send upload status
        $this->replyWithChatAction(['action' => Actions::UPLOAD_PHOTO]);

        // Get file
        $stream = Storage::readStream($webcam->path);

        // Prep file
        $file = new InputFile($stream, strtolower("{$webcam->slug}.jpg"));

        // Return message
        $this->replyWithPhoto([
            'photo' => $file,
            'caption' => sprintf('%s van %s', $webcam->name, $webcam->lastUpdate->created_at->isoFormat('ddd D MMM YYYY, HH:mm (z)')),
        ]);
    }

    protected function getCameras(): Collection
    {
        return $this->cams ??= Webcam::all();
    }
}
