<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Helpers\Arr;
use App\Helpers\Str;
use App\Models\RedirectInstruction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Yaml\Yaml;

class RedirectInstructionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $redirects = Arr::first(Yaml::parseFile(resource_path('yaml/redirects.yaml')));

        foreach ($redirects as $redirect) {
            if (! Arr::has($redirect, ['slug', 'path'])) {
                continue;
            }

            $slug = Arr::get($redirect, 'slug');
            $path = Arr::get($redirect, 'path');

            $slug = Str::start(trim($slug, '/'), '/');
            $path = URL::to($path);

            RedirectInstruction::query()
                ->withoutGlobalScopes()
                ->firstOrCreate(
                    ['slug' => $slug],
                    ['path' => $path],
                );
        }
    }
}
