<?php

declare(strict_types=1);

if (! function_exists('test_path')) {
    function test_path(string $file): ?string
    {
        $file = ltrim($file, '/');

        return base_path("tests/{$file}");
    }
}
