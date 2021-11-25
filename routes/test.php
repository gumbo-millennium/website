<?php

declare(strict_types=1);

use App\Http\Middleware\RequireActiveEnrollment;
use App\Http\Middleware\RequirePaidEnrollment;

Route::get('/test/require-active-enrollment/{activity}', fn () => 'OK')
    ->middleware([RequireActiveEnrollment::class])
    ->name('test.active-enrollment-middleware');

Route::get('/test/require-paid-enrollment/{activity}', fn () => 'OK')
    ->middleware([
        RequireActiveEnrollment::class,
        RequirePaidEnrollment::class,
    ])
    ->name('test.paid-enrollment-middleware');
