<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Runs command line commands and returns the output and exit codes.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait RunsCliCommands
{
    /**
     * Tries to run the given command
     *
     * @param array $command Command to run
     * @param string|null $stdout
     * @param string|null $stderr
     * @param int|null $timeout
     * @return int|null Exit code, or null if non-runnable
     */
    protected function runCliCommand(array $command, &$stdout = null, &$stderr = null, int $timeout = null): ?int
    {
        // Make sure the command we want to run exists
        $testProc = new Process(['which' => $command[0]]);
        $testProc->run();

        // Report a warning if it doesn't
        if (!$testProc->isSuccessful()) {
            echo "WARNING: Failed to locate {$command[0]} command!\n";
        }

        // Create process, which times out after 15 seconds
        $process = new Process($command, sys_get_temp_dir());
        $process->setTimeout($timeout ?? 15);

        // Debug start
        printf("Starting command [%s]\n", $process->getCommandLine());

        $process->run(function ($type, $buffer) {
            printf('[%s] %s', $type === Process::ERR ? 'ERR' : 'OUT', $buffer);
        });

        // Assign outputs, if present
        if ($stdout) {
            $stdout = $process->getOutput();
        }
        if ($stderr) {
            $stderr = $process->getErrorOutput();
        }

        // Return exit code
        return $process->getExitCode();
    }
}
