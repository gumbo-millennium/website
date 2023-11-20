<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class UploadToCloudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    upload-to-cloud
        {path?* : Paths to upload, defaults to user-uploads}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync files to the object storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $paths = $this->argument('path');
        if (empty($paths)) {
            $paths = ['medialibrary', 'paperclip'];
        }

        try {
            foreach ($paths as $path) {
                $this->handleDirectory($path);
            }
        } catch (RuntimeException $exception) {
            $this->line('');
            $this->error("Upload failed: {$exception->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function handleDirectory(string $directory): void
    {
        $this->comment("Uploading {$directory}...");
        $files = Storage::allFiles($directory);

        if ($this->verbosity < OutputInterface::VERBOSITY_VERBOSE) {
            $this->withProgressBar($files, fn ($file) => $this->handleFile($file));
            $this->line("\n<info>Complete</>\n");

            return;
        }

        foreach ($files as $file) {
            $this->handleFile($file);
        }
    }

    private function handleFile(string $file): void
    {
        $fromDiskName = Config::get('filesystems.default');
        $toDiskName = Config::get('filesystems.cloud');

        if ($fromDiskName == $toDiskName) {
            throw new RuntimeException('Source and destination disk are identical!');
        }

        $fromDisk = Storage::disk($fromDiskName);
        $toDisk = Storage::disk($toDiskName);

        if ($toDisk->exists($file) && $toDisk->size($file) >= $fromDisk->size($file)) {
            $this->line("<fg=blue>SKIP</> {$file}", null, OutputInterface::VERBOSITY_VERBOSE);

            return;
        }

        $this->line("<fg=green>UPL </> {$file}...", null, OutputInterface::VERBOSITY_DEBUG);

        $fromStream = null;

        try {
            $fromStream = $fromDisk->readStream($file);
            $toDisk->put($file, $fromStream);

            $this->line("<fg=green>OK  </> {$file}", null, OutputInterface::VERBOSITY_VERBOSE);
        } catch (Exception $exception) {
            $this->line("<fg=red>FAIL</> {$file}");
            $this->line(sprintf('     %s', class_basename($exception)), null, OutputInterface::VERBOSITY_DEBUG);
            $this->line(sprintf('     %s', $exception->getMessage()), null, OutputInterface::VERBOSITY_VERY_VERBOSE);
        } finally {
            if ($fromStream != null && is_resource($fromStream)) {
                fclose($fromStream);
            }
        }
    }
}
