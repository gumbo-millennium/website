<?php
declare(strict_types=1);

namespace App;

use Corcel\Model\CustomLink;
use Corcel\Model\Menu as CorcelMenu;
use Corcel\Model\MenuItem;
use Corcel\Model\Page;
use Corcel\Model\Post;
use Corcel\Model\Taxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Corcel\Model\Option;

class Menu extends Collection
{
    const LOCATION_CACHE = 'wp_menu_locations';

    /**
     * Gets a theme at the given location
     *
     * @param string $location
     * @return self|null
     */
    public static function getMenuForLocation(string $location) : ?self
    {
        $locations = static::getMenuLocations();

        if (!empty($locations[$location])) {
            return static::getMenu($locations[$location]);
        }

        return null;
    }

    /**
     * Returns menu locations for the current WordPress theme.
     *
     * @return array
     */
    protected static function getMenuLocations() : array
    {
        if (Cache::has(self::LOCATION_CACHE)) {
            return Cache::get(self::LOCATION_CACHE);
        }

        $theme = Option::get('stylesheet') ?? Option::get('current_theme') ?? 'gumbo-millennium';
        $themeConfig = Option::get("theme_mods_{$theme}") ?? Option::get("mods_{$theme}") ?? null;

        // No mods!?
        if ($themeConfig == null) {
            return null;
        }

        $menuLocations = $themeConfig['nav_menu_locations'] ?? [];

        Cache::put(self::LOCATION_CACHE, $menuLocations, now()->addMinutes(15));

        return $menuLocations;
    }

    /**
     * Finds a menu with the given slug
     *
     * @param string $slug
     * @return self|null
     */
    public static function getMenuBySlug(string $slug) : ? self
    {
        $menu = CorcelMenu::slug($slug)->first();
        return $menu ? static::build($menu) : null;
    }

    /**
     * Finds a menu with the given Taxonomy ID
     *
     * @param int $id
     * @return self|null
     */
    public static function getMenu(int $id) : ? self
    {
        $menu = CorcelMenu::find($id);
        return $menu ? static::build($menu) : null;
    }


    /**
     * Builds a Menu object from the given WordPress menu
     */
    protected static function build(CorcelMenu $menu) : ?self
    {
        // Get a mapped list of menu items, after formatting it using convertMenuItem
        $groupedMenu = $menu->items
            ->map(\Closure::fromCallable([self::class, 'convertMenuItem']))
            ->filter()
            ->groupBy('parent');

        // If the root of the menu is empty, return null
        if (!$groupedMenu->has('root') || empty($groupedMenu['root'])) {
            return null;
        }

        // Let the structureMenu handle child nodes
        return static::make(static::structureMenu($groupedMenu));
    }

    /**
     * Converts a MenuItem to a Collection with 'title', 'url', 'id', and 'parent' items.
     *
     * @param MenuItem $item
     * @return Collection
     */
    protected static function convertMenuItem(MenuItem $item) : ?Collection
    {
        $instance = $item->instance();
        $parent = $item->parent();

        // Taxonomies aren't supported
        if ($instance instanceof Taxonomy) {
            return null;
        }

        // Get link URL
        $url = $instance->url ?? null;

        // We want the slugs of the posts, but NOT for custom links, as they
        // have a special url parameter.
        if (!$instance instanceof CustomLink && !empty($instance->slug)) {
            $url = "/{$instance->slug}";
        }

        // Build the item
        return collect([
            'id' => $item->ID,
            'parent' => $parent ? $parent->ID : 'root',
            'title' => $instance->title ?? $instance->name ?? $instance->link_text,
            'url' => $url
        ]);
    }

    /**
     * Tranforms a list of Menu collections from convertMenuItem to a
     * tree-based list of nodes with children.
     *
     * @param Collection $entireMenu
     * @param int $index
     * @return Collection
     */
    protected static function structureMenu(Collection $entireMenu, int $index = null) : Collection
    {
        // Start a collection for this (sub)menu
        $menu = collect();

        // Get each item in the list
        foreach ($entireMenu[$index ?? 'root'] as $item) {
            // Handle child menus the same as this menu, if it has children
            $item['children'] = $entireMenu->has($item['id']) ? static::structureMenu($entireMenu, $item['id']) : [];

            // Add item
            $menu->push($item);
        }

        // Done ☺
        return $menu;
    }
}
