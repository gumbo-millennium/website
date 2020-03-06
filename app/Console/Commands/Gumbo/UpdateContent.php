<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Helpers\Str;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use JsonSchema\Exception\JsonDecodingException;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs an update on all files that are in the version-controlled pages
 * directory, and adds them to the pages table. Git pages cannot be modified,
 * so it also unflags pages that are no longer version controlled.
 */
class UpdateContent extends Command
{
    private const PAGE_DIRECTORY = 'assets/json/pages';
    private const PAGE_REGEX = '/^([a-z0-9\-]+)\.json$/';

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'gumbo:update-content';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Updates the pages created from Git-based files';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        // Get files
        $pages = $this->getVersionedFiles();

        // Create missing files
        $this->createOrUpdatePages($pages);

        // Release non-versioned files
        $this->releaseNonVersionedPages(array_keys($pages));

        // Print OK
        $this->info('Updated Git-based content successfully!');
    }
    /**
     * Returns a title from a slug
     * @param string $slug
     * @return string
     */
    private function buildTitle(string $slug): string
    {
        $name = Str::afterLast($slug, '-slash-');
        $name = str_replace('-', ' ', $name);
        return Str::title(trim($name));
    }

    /**
     * Creates or updates all pages in the list, marking them as non-mutable
     * @param array $pages
     * @return void
     */
    private function createOrUpdatePages(array $pages): void
    {
        // Keep track
        $updateCount = 0;

        // Loop
        foreach ($pages as $slug => $data) {
            // Get contents
            $page = Page::firstOrNew(
                ['slug' => $slug],
                ['type' => Page::TYPE_GIT]
            );

            // Assign data
            $page->created_at = $data['created_at'];
            $page->updated_at = $data['updated_at'];
            $page->contents = json_encode($data['content']);
            $page->title = $data['title'] ?? $this->buildTitle($slug);
            $page->summary = $data['summary'] ?? $data['tagline'] ?? null;

            // Save changes
            $page->save();

            // Increase count
            $updateCount++;

            // Log stuff
            $this->line(sprintf(
                "<comment>%s</> page <info>%s</> (%d)",
                $page->wasRecentlyCreated ? 'Created' : 'Updated',
                $page->slug,
                $page->id
            ), null, OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $this->line(
            "Marked <info>{$updateCount}</> page(s) as Git pages",
            null,
            OutputInterface::VERBOSITY_VERBOSE
        );
    }

    /**
     * Removes 'git' flag from pages no longer in version index
     * @param array<string> $pageSlugs
     * @return void
     */
    private function releaseNonVersionedPages(array $pageSlugs): void
    {
        // Mark existing files
        $existingPages = Page::query()
            ->whereType(Page::TYPE_GIT)
            ->whereNull('group')
            ->whereNotIn('slug', $pageSlugs)
            ->get();

        // Keep track
        $updateCount = 0;

        // Fetch required pages
        $requiredPages = Page::getRequiredPages();

        // Mark all pages as required or user, but remove the git flag anyway.
        foreach ($existingPages as $page) {
            // Change type if not empty
            $page->type = \array_key_exists($page->slug, $requiredPages) ? Page::TYPE_REQUIRED : Page::TYPE_USER;
            $page->save(['type']);

            // Increase count
            $updateCount++;

            // Log stuff
            $this->line(sprintf(
                "Unmarked page <info>%s</> (%d)",
                $page->slug,
                $page->id
            ), null, OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $this->line(
            "Marked <info>{$updateCount}</> page(s) as non-Git pages",
            null,
            OutputInterface::VERBOSITY_VERBOSE
        );
    }

    /**
     * Returns list of pages versioned in the code
     * @return array<array<mixed>>
     */
    private function getVersionedFiles(): array
    {
        // Get path
        $directory = resource_path(self::PAGE_DIRECTORY);

        // Skip if missing
        if (!\file_exists($directory) || !\is_dir($directory)) {
            dd("No directory found!");
            return [];
        }

        // Get files
        $files = scandir($directory);

        // Return if scan failed
        if (!$files) {
            return [];
        }

        // Map
        $foundFiles = [];

        // Loop files
        foreach ($files as $file) {
            if (!\preg_match(self::PAGE_REGEX, $file, $matches)) {
                continue;
            }

            try {
                // Get path
                $path = $directory . DIRECTORY_SEPARATOR . $file;

                // Ensure readability
                if (!\is_file($path)) {
                    // Create a runtime exception with info
                    $exception = new RuntimeException("Cannot read {$path} or it's not a file.");

                    // Report it to Laravel
                    report($exception);

                    // Print it too
                    $this->error($exception->getMessage());

                    // Try next file
                    continue;
                }

                // Get JSON
                $fileData = json_decode(file_get_contents($path), true);

                // Parse dates
                $fileData['created_at'] = $this->parseDate($fileData, 'created_at', $path);
                $fileData['updated_at'] = $this->parseDate($fileData, 'updated_at', $path);

                // Assign data
                $foundFiles[$matches[1]] = $fileData;
            } catch (JsonDecodingException $exception) {
                // Change into a runtime exception with more info
                $exception = new RuntimeException(
                    "Failed to parse JSON in {$path}: {$exception->getMessage()}",
                    $exception->getCode(),
                    $exception
                );

                // Report it to Laravel
                report($exception);

                // Print it too
                $this->error($exception->getMessage());
            }
        }

        // Return
        return $foundFiles;
    }

    /**
     * Processes the data from the file
     * @param array $data
     * @param string $key
     * @param string $path
     * @return Carbon
     */
    private function parseDate(array $data, string $key, string $path): Carbon
    {
        // Check key first
        $value = $data[$key] ?? null;

        // If missing or empty, use file date
        if (empty($value)) {
            $value = date('Y-m-d', filemtime($path));
        }

        try {
            // Parse as ISO8601 date and set time to midnight
            return Carbon::createFromFormat('Y-m-d', $value)->setTime(0, 0, 0);
        } catch (\Throwable $exception) {
            // Report error
            report(new RuntimeException(
                "Failed to parse date in {$path}: {$exception->getMessage()}",
                $exception->getCode(),
                $exception
            ));

            // Return today
            return Carbon::today();
        }
    }
}
