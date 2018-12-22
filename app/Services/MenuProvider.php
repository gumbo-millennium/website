<?php
declare(strict_types=1);

namespace App\Services;

use Corcel\Model\Menu as CorcelMenu;
use App\Menu;
use Corcel\Model\Option;
use Illuminate\Support\Facades\Cache;

/**
 * Handles finding menus by ID, slug or location.
 *
 * @author Roelof Roos
 * @license MPL-2.0
 */
class MenuProvider
{
    const LOCATION_CACHE = 'wp_menu_locations';

    /**
     * Returns menu locations for the current WordPress theme.
     *
     * @param bool|null $forceRefresh Ignore cache
     * @return array
     */
    protected static function getMenuLocations(bool $forceRefresh = null) : array
    {
        if (Cache::has(self::LOCATION_CACHE) && !$forceRefresh) {
            return Cache::get(self::LOCATION_CACHE);
        }

        $theme = Option::get('stylesheet') ?? Option::get('current_theme') ?? 'gumbo-millennium';
        $themeConfig = Option::get("theme_mods_{$theme}") ?? Option::get("mods_{$theme}") ?? null;

        // No mods!?
        if ($themeConfig == null) {
            return null;
        }

        $menuLocations = $themeConfig['nav_menu_locations'] ?? [];

        Cache::put(self::LOCATION_CACHE, $menuLocations, now()->addDays(7));

        return $menuLocations;
    }

    /**
     * Returns if the given menu location exists.
     *
     * @param string $location
     * @return bool
     */
    public function hasLocation(string $location) : bool
    {
        $locations = static::getMenuLocations();

        return !empty($locations[$location]);
    }

    /**
     * Gets a theme at the given location. Cached
     *
     * @param string $location
     * @return Menu|null
     */
    public function location(string $location) : ?Menu
    {
        // Load menu locations
        $locations = static::getMenuLocations();

        // Check if menu location is valid
        if (empty($locations[$location])) {
            return null;
        }

        // Get cache key
        $cacheKey = "menu.{$location}";

        // Load menu from cache
        if (Cache::tags('menu-locations')->has($cacheKey)) {
            return Cache::tags('menu-locations')->get($cacheKey);
        }

        // Get menu
        $menu = $this->id($locations[$location]);

        // Cache menu
        Cache::tags(['menu-locations', 'wordpress'])->forever($cacheKey, $menu);

        // Return menu
        return $menu;
    }

    /**
     * Finds a menu with the given slug
     *
     * @param string $slug
     * @return self|null
     */
    public function slug(string $slug) : ?Menu
    {
        $menu = CorcelMenu::slug($slug)->first();
        return $menu ? new Menu($menu) : null;
    }

    /**
     * Finds a menu with the given Taxonomy ID
     *
     * @param int $id
     * @return self|null
     */
    public function id(int $id) : ?Menu
    {
        $menu = CorcelMenu::find($id);
        return $menu ? new Menu($menu) : null;
    }
}
