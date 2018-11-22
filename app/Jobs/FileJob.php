<?php

declare (strict_types = 1);

namespace App\Jobs;

use App\File;
use App\Jobs\Concerns\ReplacesStoredFiles;
use App\Jobs\Concerns\RunsCliCommands;
use App\Jobs\Concerns\UsesTemporaryFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Basic file job, specifies the correct queue
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class FileJob implements ShouldQueue
{
    use
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        ReplacesStoredFiles,
        RunsCliCommands,
        SerializesModels,
        UsesTemporaryFiles;
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'files';

    /**
     * File being processed
     *
     * @var File
     */
    protected $file;

    /**
     * Create a new job instance.
     *
     * @param File $file File to process
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void|boolean
     */
    abstract public function handle() : ?bool;
}
