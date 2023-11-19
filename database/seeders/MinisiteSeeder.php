<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Models\Minisite\PageType;
use App\Models\Minisite\Site;
use App\Models\Page;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class MinisiteSeeder extends Seeder
{
    private const REQUIRED_PAGES = [
        'home' => 'Welkom op %s',
        'about' => 'Over %s',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $config = Yaml::parseFile(resource_path('yaml/minisites.yaml'));
        $sites = Collection::make();

        $existingRoles = Role::query()->pluck('name');

        foreach ($config['sites'] as $index => $siteConfig) {
            throw_unless(Arr::has($siteConfig, ['domain', 'name', 'group']), RuntimeException::class, "Invalid site definition on index [{$index}]");

            $site = Site::firstOrNew(['domain' => $siteConfig['domain']], [
                'name' => $siteConfig['name'],
                'enabled' => false,
            ]);

            $site->save();

            try {
                $site->group()->associate(Role::findByName($siteConfig['group']));
            } catch (Throwable $e) {
                Log::error("Invalid group [{$siteConfig['group']}] for site [{$site->name}]");
            }

            $sites->push($site);
        }

        /**
         * Drop all sites that are not in the config.
         */
        Site::whereNotIn('domain', $sites->pluck('domain'))->delete();

        /**
         * Ensure all minisites have a home and about page.
         */
        foreach ($sites as $site) {
            foreach (self::REQUIRED_PAGES as $slug => $title) {
                // Find or create the page
                $page = $site->pages()->firstOrNew([
                    'slug' => $slug,
                ], [
                    'title' => sprintf($title, $site->name),
                ]);

                // Set some required params
                $page->visible = true;
                $page->type = PageType::Required;

                // Un-trash the page if it's trashed
                $page->trashed() and $page->restore();

                // Save the page
                $page->save();
            }
        }
    }
}
