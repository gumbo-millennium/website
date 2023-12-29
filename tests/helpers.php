<?php

declare(strict_types=1);

if (! function_exists('test_path')) {
    /**
     * Returns absolute path to a file in the tests folder.
     */
    function test_path(string $file): string
    {
        $file = ltrim($file, '/');

        return base_path("tests/{$file}");
    }
}

if (! function_exists('test_resource')) {
    /**
     * Returns absolute path to a test resource, or the contents of it.
     */
    function test_resource(string $file, bool $returnContents = false): string
    {
        $file = ltrim($file, '/');

        $path = test_path("Fixtures/resources/{$file}");

        return $returnContents ? file_get_contents($path) : $path;
    }
}
