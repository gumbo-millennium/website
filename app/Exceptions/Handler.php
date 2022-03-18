<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        EnrollmentNotFoundException::class,
    ];

    /**
     * A list of the exception types that are not reported when running in console.
     *
     * @var string[]
     */
    protected $dontReportCli = [
        ExceptionInterface::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Exception $exception
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     * Extended from Laravel to allow for a CLI-exception list.
     *
     * @return bool
     */
    protected function shouldntReport(Throwable $e)
    {
        // Firstly check upstream
        if (parent::shouldntReport($e)) {
            return true;
        }

        // Then check for CLI-exceptions, like invalid commands I type on a prod environment
        if (PHP_SAPI == 'cli') {
            return null !== Arr::first($this->dontReportCli, fn ($type) => $e instanceof $type);
        }

        // Probably want to report this now
        return false;
    }
}
