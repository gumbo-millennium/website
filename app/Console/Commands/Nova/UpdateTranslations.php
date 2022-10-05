<?php

declare(strict_types=1);

namespace App\Console\Commands\Nova;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpdateTranslations extends Command
{
    public const TARGET_FILE = 'lang/vendor/nova/%s.json';

    public const DOWNLOAD_URL = 'https://github.com/coderello/laravel-nova-lang/raw/1.0/resources/lang/%s.json';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova:update-translations {language}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the translations by downloading the latest translations from Nova and merging them with "laravel-nova-lang"';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $targetFile = resource_path(sprintf(self::TARGET_FILE, $this->argument('language')));

        $this->line('Downloading external translations...');
        $externalFile = Http::get(sprintf(self::DOWNLOAD_URL, $this->argument('language')));
        if (! $externalFile->successful()) {
            $this->warn('Failed to download remote translations file');

            return Command::FAILURE;
        }
        $externalLanguageLines = json_decode($externalFile->body(), true, 64, JSON_THROW_ON_ERROR);

        $this->line('Loading existing translations...');
        $existingLanguageLines = json_decode(file_get_contents($targetFile), true, 64, JSON_THROW_ON_ERROR);

        $this->line('Pulling new translations from Nova');
        Artisan::call('nova:translate', [
            'language' => $this->argument('language'),
            '--force' => true,
        ]);

        $this->line('Loading new translations...');
        $newLanguageLines = json_decode(file_get_contents($targetFile), true, 64, JSON_THROW_ON_ERROR);

        $this->line('Merging translations...');
        $newTranslations = [];

        $existing = [];
        $downloaded = [];
        $new = [];

        foreach ($newLanguageLines as $key => $newValue) {
            $existingMatch = $this->deepSearch($existingLanguageLines, $key);
            if ($existingMatch && $existingMatch !== $newValue) {
                $existing[] = $key;
                $newTranslations[$key] = $existingMatch;

                continue;
            }

            if ($downloadedMatch = $this->deepSearch($externalLanguageLines, $key)) {
                $downloaded[] = $key;
                $newTranslations[$key] = $downloadedMatch;

                continue;
            }

            $new[] = $key;
            $newTranslations[$key] = $newValue;
        }

        $existingCount = count($existing);
        $downloadedCount = count($downloaded);
        $newCount = count($new);

        $this->line("Merging complete. {$existingCount} up-to-date, {$downloadedCount} downloaded, leaving <info>{$newCount} new</> translations");

        if ($this->confirm('Would you like to define the new translations now?')) {
            $this->line('Please enter the translations for the following keys:');
            foreach ($new as $index => $newLine) {
                $newTranslations[$newLine] = $this->ask(sprintf("(%02d / %02d) \n<comment>\"%s\"</>", $index + 1, $newCount, $newLine)) ?: $newLine;
            }
        }

        $this->line('Writing translations...');
        file_put_contents($targetFile, json_encode($newTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return 0;
    }

    private function deepSearch(array $strings, string $needle): ?string
    {
        // Count the dots
        preg_match('/([.]*)$/', $needle, $matches);
        $requiredDots = $matches[1];

        // Find all alternatives
        $options = [
            rtrim($needle, '.'),
            Str::finish($needle, '.'),
            Str::finish($needle, '...'),
        ];

        // Match the first
        $result = Arr::first(Arr::only($strings, $options));
        if (! $result) {
            return null;
        }

        // Re-append the dots
        return rtrim($result, '.') . $requiredDots;
    }
}
