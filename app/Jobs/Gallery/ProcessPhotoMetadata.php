<?php

declare(strict_types=1);

namespace App\Jobs\Gallery;

use App\Models\Gallery\Photo;
use Carbon\Exceptions\InvalidDateException;
use finfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoMetadata implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Photo $photo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get photo
        $photo = $this->photo;

        // Load the photo into a temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'gumbo');
        file_put_contents($tempFile, Storage::disk(Config::get('gumbo.images.disk'))->get($photo->path));

        // Get mime
        $mime = finfo_file(new finfo(FILEINFO_MIME_TYPE), $tempFile);

        try {
            // Handle upon file
            switch($mime) {
                case 'image/jpeg':
                    $this->figureOutTakenAtDate($photo, $tempFile);
                    // no break
                case 'image/png':
                case 'image/gif':
                case 'image/webp':
                    $this->updateImageDimensions($photo, $tempFile);
            }
        } finally {
            // Delete temp file
            @unlink($tempFile);
        }

        // Persist changes
        if ($photo->isDirty()) {
            $photo->save();
        }
    }

    /**
     * Update the image dimensions and size.
     * @param Photo $photo Photo file to update
     * @param string $tempFile File to check
     * @return void Nothing is returned, but the Photo object is updated
     */
    private function updateImageDimensions(Photo &$photo, string $tempFile): void
    {
        if ($imageSize = getimagesize($tempFile)) {
            [$width, $height] = $imageSize;

            $photo->width = $width;
            $photo->height = $height;
        }

        $photo->size = filesize($tempFile);
    }

    /**
     * Figure out the taken at date from the exif data.
     * @param Photo $photo Photo file to update
     * @param string $tempFile File to read
     * @return void Nothing is returned, but the Photo object is updated
     */
    private function figureOutTakenAtDate(Photo &$photo, string $tempFile): void
    {
        $data = exif_read_data($tempFile);
        if (($data = exif_read_data($tempFile)) === false) {
            return;
        }

        if (! $exifDate = $data['DateTimeOriginal'] ?? $data['DateTimeDigitized'] ?? $data['DateTime'] ?? null) {
            return;
        }

        try {
            $takenDate = Date::createFromFormat('Y:m:d H:i:s', $exifDate);

            // Update taken_at if it's different
            if ($takenDate) {
                $photo->taken_at = $takenDate;
            }
        } catch (InvalidDateException) {
            // Ignore
        }
    }
}
