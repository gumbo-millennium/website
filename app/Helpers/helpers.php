<?php

declare(strict_types=1);

use App\Helpers\Str;

if (!function_exists('mix_file')) {
    function mix_file(string $file): ?string
    {
        $url = (string) mix($file);
        if (empty($url)) {
            return null;
        }

        $path = Str::after($url, (string) app('config')->get('app.mix_url'));
        if (empty($path)) {
            return null;
        }

        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath, false, null, 0, 1024 * 1024);
    }
}
