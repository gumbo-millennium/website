<?php
declare(strict_types=1);

namespace App;

use Corcel\Model\CustomLink;
use Corcel\Model\Menu as CorcelMenu;
use Corcel\Model\MenuItem;
use Corcel\Model\Taxonomy;
use Illuminate\Support\Collection;

class Menu extends Collection
{
    /**
     * Converts a MenuItem to a Collection with 'title', 'url', 'id', and 'parent' items.
     *
     * @param MenuItem $item
     * @return Collection
     */
    protected static function convertMenuItem(MenuItem $item) : ? Collection
    {
        // Get the instance for the menu item, see Corcel docs.
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

    /**
     * Creates a new Menu collection
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        // Convert menu if given
        if ($items instanceof CorcelMenu) {
            $items = $this->build($items);
        }

        parent::__construct($items);
    }

    /**
     * Builds a Menu object from the given WordPress menu
     */
    protected function build(CorcelMenu $menu) : ?Collection
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
        return static::structureMenu($groupedMenu);
    }
}
