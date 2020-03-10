<?php

declare(strict_types=1);

namespace App\Jobs\Concerns;

use Symfony\Component\Process\Process;

/**
 * Runs command line commands and returns the output and exit codes.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait RunsCliCommands
{
    /**
     * Tries to run the given command
     * @param array $command Command to run
     * @param string|null $stdout
     * @param string|null $stderr
     * @param int|null $timeout
     * @param string|resource|null $stdin
     * @return int|null Exit code, or null if non-runnable
     */
    protected function runCliCommand(
        array $command,
        &$stdout = null,
        &$stderr = null,
        ?int $timeout = null,
        &$stdin = null
    ): ?int {
        // Make sure the command we want to run exists
        $testProc = new Process(['which', $command[0]]);
        $testProc->setInput($stdin);
        $testProc->run();

        // Report a warning if it doesn't
        if (!$testProc->isSuccessful()) {
            $stdout = $testProc->getOutput();
            $stderr = $testProc->getErrorOutput();

            logger()->info("Failed to locate executable {executable}!", [
                'executable' => $command[0],
                'command' => $command,
                'stdout' => $stdout,
                'stderr' => $stderr
            ]);
            return 255;
        }

        // Create process, which times out after 15 seconds
        $process = new Process($command, sys_get_temp_dir());
        $process->setTimeout($timeout ?? 15);

        // Only debug on local
        if (app()->isLocal()) {
            // Debug start
            logger()->debug(sprintf("RUN> %s\n", $process->getCommandLine()));

            // Print each line with a prefix
            $process->run(static function ($type, $buffer) {
                logger()->debug(printf('%s> %s', $type === Process::ERR ? 'ERR' : 'OUT', trim($buffer)));
            });
        }

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
