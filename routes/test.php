<?php

declare(strict_types=1);

use App\Http\Middleware\RequireActiveEnrollment;

Route::get('/test/require-active-enrollment/{activity}', fn () => 'OK')
    ->middleware([RequireActiveEnrollment::class])
    ->name('test.active-enrollment-middleware');
