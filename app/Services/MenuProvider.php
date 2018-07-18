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
     * Gets a theme at the given location
     *
     * @param string $location
     * @return self|null
     */
    public function location(string $location) : ?Menu
    {
        $locations = static::getMenuLocations();

        if (!empty($locations[$location])) {
            return $this->id($locations[$location]);
        }

        return null;
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
