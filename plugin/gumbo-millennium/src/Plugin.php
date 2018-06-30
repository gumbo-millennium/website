<?php
declare(strict_types=1);

namespace Gumbo\Plugin;

use Gumbo\Plugin\PostTypes\ActivityType;
use Gumbo\Plugin\PostTypes\PostType;

/**
 * Boots the plugin, which /should/ be loaded as a must-use plugin. This means
 * there are no installation and uninstallation hooks available!
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class Plugin
{
    /**
     * Launches the plugin, by registering all bindings.
     */
    public function boot() : void
    {
        // Bind hooks
        $this->bindHooks();

        // Bind shortcodes
        $this->bindActions();

        // Bind shortcodes
        $this->bindShortcodes();
    }

    /**
     * Handles WordPress bindings for shortcodes.
     */
    protected function bindShortcodes() : void
    {
        // TODO
    }

    /**
     * Handles WordPress bindings for hooks.
     */
    protected function bindHooks() : void
    {
        // Handle registering custom post types
        add_action('init', \Closure::fromCallable([$this, 'registerPostTypes']));

        // TODO Add more items, if required
    }

    /**
     * Handles WordPress bindings for actions.
     */
    protected function bindActions() : void
    {
        // TODO
    }

    /**
     * Register custom post types, which are used for the activities and files
     *
     * @return void
     */
    protected function registerPostTypes() : void
    {
        // List post types
        $types = [
            new ActivityType
        ];

        foreach ($types as $type) {
            if ($type instanceof PostType) {
                $type->registerType();
            }
        }
    }
}
