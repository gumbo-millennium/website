<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Gallery;

use App\Services\GalleryExifService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DownloadExifModelMap extends Command
{
    private const DATABASE_URL = 'http://storage.googleapis.com/play_public/supported_devices.csv';

    private const LOCAL_COPY_URL = 'gumbo/gallery/exif-model-map.csv';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        gumbo:gallery:exif:download
            {--fresh : Download a fresh copy of the device map}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads a database mapping Smartphone model codes to their device name.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Download the device map, if required
        if (! Storage::has(self::LOCAL_COPY_URL) || $this->option('fresh')) {
            if (! $this->downloadDeviceMap()) {
                return 1;
            }
        }

        // Get service
        $service = App::make(GalleryExifService::class);

        // Compute the device map
        if (! $this->buildDeviceMap($service)) {
            return 1;
        }

        return 0;
    }

    private function downloadDeviceMap(): bool
    {
        $request = Http::get(self::DATABASE_URL);

        // Ensure request was successful
        if (! $request->successful()) {
            $this->error('Failed to download device map.');

            return false;
        }

        // Convert to good data format
        $devices = iconv('UTF-16LE', 'UTF-8', $request->body());

        // Save initial data to disk
        Storage::put(self::LOCAL_COPY_URL, $devices);
        $this->info('Device map downloaded.');

        return true;
    }

    private function buildDeviceMap(GalleryExifService $service): bool
    {
        $databaseStream = Storage::readStream(self::LOCAL_COPY_URL);

        try {
            $service->parseDatabaseFromCsv($databaseStream);
            $this->info('Device map built.');
        } catch(RuntimeException $exception) {
            $this->error('Failed to parse device map!');
            $this->line($exception->getMessage());

            return false;
        }

        return true;
    }
}
