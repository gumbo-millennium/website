<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\File;
use App\Jobs\FileMetaJob;
use App\Jobs\FileThumbnailJob;
use App\Jobs\FileRepairJob;
use Illuminate\Console\Command;

class TestFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:file {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests a file processing job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() : void
    {
        // Find file
        $file = File::findOrFail($this->argument('file'));

        // Fire job
        $this->line('File repair job');
        (new FileRepairJob($file))->handle();

        $this->line('File meta job');
        (new FileMetaJob($file))->handle();

        $this->line('File thumnnail job');
        (new FileThumbnailJob($file))->handle();

        // Report job
        $this->line('Repair job scheduled');
    }
}
