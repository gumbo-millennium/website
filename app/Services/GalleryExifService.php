<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Arr;
use App\Helpers\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use resource;
use RuntimeException;

final class GalleryExifService
{
    private string $databasePath;

    private ?array $database = null;

    /**
     * Cleans up strings, removing non-ascii characters and trimming the ends.
     */
    private static function normalize(?string $string): string
    {
        if ($string === null) {
            return null;
        }

        return (string) Str::of($string)->ascii()->trim();
    }

    /**
     * Reads a single CSV line from the stream, normalizes it, and returns it as an array.
     * @param resource $stream
     * @return null|array normalized CSV line, or null if the end of the stream was reached
     */
    private static function readCsvLine($stream): ?array
    {
        $data = fgetcsv($stream, 0, ',');
        if ($data === false) {
            return null;
        }

        return array_map(fn ($row) => self::normalize($row), $data);
    }

    public function __construct(?string $databasePath = null)
    {
        $this->databasePath = $databasePath ?? Config::get('gumbo.gallery.exif.database_path');
    }

    /**
     * @param resource $csvReadStream
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function parseDatabaseFromCsv($csvReadStream): void
    {
        $resourceType = is_resource($csvReadStream) ? get_resource_type($csvReadStream) : null;
        if ($resourceType !== 'stream') {
            throw new InvalidArgumentException('Expected a stream resource.');
        }

        $headerLine = self::readCsvLine($csvReadStream);
        $brandIndex = array_search('Retail Branding', $headerLine, true);
        $displayName = array_search('Marketing Name', $headerLine, true);
        $modelIndex = array_search('Model', $headerLine, true);

        if ($brandIndex === false || $displayName === false || $modelIndex === false) {
            throw new RuntimeException('Failed to parse device map.');
        }

        $deviceMap = [];
        while (($row = self::readCsvLine($csvReadStream)) !== null) {
            $rowBrand = $row[$brandIndex] ?? null;
            $rowModel = $row[$modelIndex] ?? null;
            $rowDisplayName = $row[$displayName] ?? null;

            if ($rowBrand === null || $rowModel === null || $rowDisplayName === null) {
                continue;
            }

            // Removes excess brand at start of model, e.g. "Nokia 3310" -> "3310"
            // Otherwise, the display value would be "Nokia Nokia 3310"
            $rowDisplayNameWithoutBrand = Str::startsWith($rowDisplayName, $rowBrand)
                ? self::normalize(Str::after($rowDisplayName, $rowBrand))
                : $rowDisplayName;

            $rowModelLower = strtolower($rowModel);
            $rowBrandLower = strtolower($rowBrand);

            // Also match occurances of "Nokia 3310" with "3310"
            // The model code might not contain the brand name.
            $rowModelLowerWithoutBrand = Str::startsWith($rowModelLower, $rowBrandLower)
                ? Str::after($rowModelLower, $rowBrandLower)
                : $rowModelLower;

            // Create a _brand key with the proper cased brand name
            $deviceMap[$rowBrandLower] ??= [
                '_brand' => $rowBrand,
            ];

            // Store both entires, which might be the same, but that won't hurt anything
            $deviceMap[$rowBrandLower][$rowModelLower] = $rowDisplayNameWithoutBrand;
            $deviceMap[$rowBrandLower][$rowModelLowerWithoutBrand] = $rowDisplayNameWithoutBrand;
        }

        // Write out data
        Storage::put($this->databasePath, json_encode($deviceMap, JSON_PRETTY_PRINT));

        // Prune cached DB
        $this->database = null;
    }

    /**
     * Determines the proper display name of the make and model, using the database.
     */
    public function determineDisplayMakeAndModel(string $make, string $model): array
    {
        // Use consistent casing
        $makeLower = strtolower(self::normalize($make) ?? '');
        $modelLower = strtolower(self::normalize($model) ?? '');

        // Get DB handle
        $database = $this->getDatabase();

        // Get them, simple enough
        return [
            'make' => Arr::get($database, "{$makeLower}._brand", $make),
            'model' => Arr::get($database, "{$makeLower}.{$modelLower}", $model),
        ];
    }

    /**
     * Returns the datbase, as a singleton.
     */
    private function getDatabase(): array
    {
        if (! Storage::has($this->databasePath)) {
            return $this->database = [];
        }

        // Get database, from JSON
        return $this->database = json_decode(Storage::get($this->databasePath), true);
    }
}
