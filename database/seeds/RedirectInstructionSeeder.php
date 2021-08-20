<?php

declare(strict_types=1);

use App\Models\RedirectInstruction;
use Illuminate\Database\Seeder;
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

            $redirectInstance = RedirectInstruction::query()
                ->withoutGlobalScopes()
                ->firstOrNew(
                    ['slug' => Arr::get($redirect, 'slug')],
                    ['path' => Arr::get($redirect, 'path')],
                );

            if (! $redirectInstance->exists) {
                $redirectInstance->save();
            }
        }
    }
}
