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

        // Get exif info about taken date
        $takenDate = null;
        if ($mime === 'image/jpeg' && ($data = exif_read_data($tempFile)) !== false) {
            if ($exifDate = $data['DateTimeOriginal'] ?? $data['DateTimeDigitized'] ?? $data['DateTime'] ?? null) {
                try {
                    $takenDate = Date::createFromFormat('Y:m:d H:i:s', $exifDate);

                    // Update taken_at if it's different
                    if ($takenDate) {
                        $photo->taken_at = $takenDate;
                        $photo->save();
                    }
                } catch (InvalidDateException) {
                    // Ignore
                }
            }
        }

        // Delete temp file
        @unlink($tempFile);
    }
}
