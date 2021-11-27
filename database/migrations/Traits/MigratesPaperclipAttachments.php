<?php

declare(strict_types=1);

namespace Database\Migrations\Traits;

use App\Helpers\Str;
use Czim\Paperclip\Attachment\Attachment;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Exception;

trait MigratesPaperclipAttachments
{
    protected function migrateAttachments(string $model, string $fromField, string $toField): void
    {
        // Skip if Paperclip is no longer available.
        if (! class_exists(Attachment::class)) {
            return;
        }

        $destinationPath = sprintf(
            'images/%s/%s',
            Str::slug(Str::plural(class_basename($model))),
            Str::slug($toField),
        );

        /** @var Activity $activity */
        foreach ($model::query()->withoutGlobalScopes()->cursor() as $activity) {
            /** @var \Czim\Paperclip\Attachment\Attachment $image */
            $image = $activity->{$fromField};

            if (! $image || ! $image instanceof Attachment || ! $image->exists()) {
                continue;
            }

            try {
                if (! $imageStream = Storage::disk($image->getStorage())->readStream($image->path())) {
                    continue;
                }

                $imageName = sprintf('%s/%s.%s', $destinationPath, Str::random(40), Str::afterLast($image->path(), '.'));

                if (Storage::disk('public')->putStream($imageName, $imageStream)) {
                    $activity->{$toField} = $imageName;
                    $activity->save();
                }
            } catch (Exception $filesystemException) {
                report($filesystemException);

                // Ignore
            } finally {
                if (is_resource($imageStream)) {
                    @fclose($imageStream);
                }
            }
        }
    }
}
