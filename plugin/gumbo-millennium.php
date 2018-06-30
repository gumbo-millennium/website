<?php
declare(strict_types=1);
/**
 * Gumbo Millennium plugin for Wordpress
 *
 * Plugin Name:     Gumbo Millennium - Custom Post Types
 * Description:     Activiteit en documenttypes voor WordPress
 * Version:         1.0.0
 * Author:          Digitale Commissie
 * License:         MPL-2.0
 * License URI:     https://www.mozilla.org/en-US/MPL/2.0/
 */

use Gumbo\Plugin\Plugin;

if (!defined('ABSPATH')) {
    // Decline loading the file, if we're not in a WordPress environment
    http_response_code(403);
    exit;
}

// Load autolaoder
require_once __DIR__ . '/gumbo-millennium/vendor/autoload.php';

// Proxy loader
$plugin = new Plugin;
$plugin->boot();
