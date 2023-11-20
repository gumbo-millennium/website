<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityExportType;
use App\Excel\Exports\ActivityParticipantsExport;
use App\Excel\Exports\ActivityParticipantsFullExport;
use App\Excel\Exports\ActivityParticipantsPresenceExport;
use App\Helpers\Str;
use App\Models\Activity;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

final class ActivityExportService
{
    private const EXPORT_DIRECTORY = 'exports/activities';

    /**
     * Create an export of the given activity of the given type,
     * returns a string with the path to the file on success.
     * @throws RuntimeException
     */
    public function createParticipantsExport(Activity $activity, ActivityExportType $exportType = ActivityExportType::CheckIn): string
    {
        $exportClass = match ($exportType) {
            ActivityExportType::CheckIn => ActivityParticipantsPresenceExport::class,
            ActivityExportType::Full => ActivityParticipantsFullExport::class,
        };

        assert(is_a($exportClass, ActivityParticipantsExport::class, true));

        /** @var ActivityParticipantsExport $export */
        $export = new $exportClass($activity);

        // Determine temp name
        $tempName = sprintf('%s/temp/%s.ods', self::EXPORT_DIRECTORY, Str::uuid());
        $exportName = sprintf('%s/participants/%s-%s.ods', self::EXPORT_DIRECTORY, $activity->slug, $exportType->name);

        // Write to a temp location
        if (! ExcelFacade::store($export, $tempName, Storage::getDefaultCloudDriver(), Excel::ODS, [
            'visibiliy' => 'private',
        ])) {
            throw new RuntimeException('Failed to write to temporary location');
        }

        // Move to final location, removing the existing file if it exists
        Storage::cloud()->delete($exportName);

        // Move file
        if (! Storage::cloud()->move($tempName, $exportName)) {
            throw new RuntimeException('Failed to move file to final location');
        }

        return $exportName;
    }

    /**
     * Remove old exports.
     */
    public function pruneExports(): void
    {
        $disk = Storage::cloud();

        $exportFiles = $disk->allFiles(self::EXPORT_DIRECTORY);

        foreach ($exportFiles as $file) {
            $lastModified = Date::createFromTimestamp($disk->lastModified($file));

            if (Date::now()->diffInHours($lastModified) > 2) {
                $disk->delete($file);
            }
        }
    }
}
