<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Gallery;

use App\Jobs\Gallery\ProcessPhotoMetadata;
use App\Models\Gallery\Photo;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPhoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:gallery:process
        {file : File to process}
        {--all : Process all files}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocess metadata on the given photo ID (or all photos)';

    /**
     * Ensure no error is thrown if the --all option is specified.
     */
    public function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('all')) {
            $input->setArgument('file', '*');
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $photos = Photo::query()
            ->unless($this->option('all'), fn ($query) => $query->whereId($this->argument('file')))
            ->with('album:id,name')
            ->lazy(50);

        foreach ($photos as $photo) {
            try {
                ProcessPhotoMetadata::dispatchSync($photo);
                $this->line("Processed <info>{$photo->id}</> (part of {$photo->album->name})");
            } catch (Exception $exception) {
                $this->error("Failed to process <error>{$photo->id}</> (part of {$photo->album->name})");
                $this->line($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
