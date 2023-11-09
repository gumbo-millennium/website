<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class TenorGifService
{
    /**
     * Tenor API key. The API won't work without a key, so a null value
     * should be considered as "not configured".
     */
    public function getApiKey(): ?string
    {
        return Config::get('services.tenor.api_key');
    }

    /**
     * Returns an API key to identify this client.
     */
    public function getClientApiKey(): string
    {
        return parse_url(Config::get('app.url'), PHP_URL_HOST);
    }

    /**
     * Terms to pre-load.
     */
    public function getTerms(): array
    {
        return Config::get('services.tenor.terms', []);
    }

    /**
     * Get the config for a single pre-loaded term.
     */
    public function getTermConfig(string $term): ?array
    {
        return Arr::get($this->getTerms(), $term);
    }

    /**
     * @return FilesystemAdapter|FilesystemContract
     */
    public function getDisk(): FilesystemContract
    {
        return Storage::disk(Config::get('services.tenor.storage.disk', 'public'));
    }

    /**
     * Returns the base directory for the gifs.
     */
    public function getGifBaseDirectory(): string
    {
        return Config::get('services.tenor.storage.path', 'gifs/storage');
    }

    /**
     * Stores the given file in the Gif group.
     * @param string $group Group to store in
     * @param File $file File to store (will be preserved)
     * @param string $id tenor ID of the file
     * @return string Path to the file
     * @throws RuntimeException if the writing has failed
     */
    public function putGifInGroup(string $group, File $file, string $id): string
    {
        $path = $this->getDisk()->putFileAs(
            $this->getGifBaseDirectory() . '/' . $this->hash($group),
            $file,
            "{$id}.{$file->guessExtension()}",
            ['visibility' => 'public'],
        );

        return $path !== false ? $path : throw new RuntimeException('Failed to store file on disk!');
    }

    /**
     * Returns the disk apth to a random gif from the given group (if available).
     * @param string $group Group to fetch from
     * @return string path on the disk to the gif
     * @throws RuntimeException if the group is empty
     */
    public function getGifPathFromGroup(string $group): string
    {
        throw_unless($group === Str::slug($group), InvalidArgumentException::class, 'Group name must be slugified');

        $path = $this->getGifBaseDirectory() . '/' . $this->hash($group);

        $files = $this->getDisk()->allFiles($path);

        if (empty($files)) {
            throw new RuntimeException('No gifs available from this directory');
        }

        return Arr::random($files);
    }

    /**
     * Returns a (temporary) URL to a random gif from the given group (if available).
     * @param string $group Group to fetch from
     * @return string URL (temporary if possible) to the gif
     * @throws RuntimeException if the group is empty
     */
    public function getGifUrlFromGroup(string $group): string
    {
        return $this->getDisk()->url($this->getGifPathFromGroup($group));
    }

    /**
     * Returns a hash to store gifs in.
     */
    private function hash(string $value): string
    {
        return substr(hash('sha256', $value), 0, 8);
    }
}
