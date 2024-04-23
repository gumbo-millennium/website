<?php

declare(strict_types=1);

use App\Fluent\Image;
use App\Helpers\Str;

$appHost = parse_url(env('APP_URL') ?? '', PHP_URL_HOST);

return [
    // Application status, based on URL
    'beta' => env('GUMBO_BETA', $appHost !== 'www.gumbo-millennium.nl'),

    // Cost of a single payment transaction
    'transfer-fee' => 40,

    /**
     * ID of the user to use for admin tasks.
     */
    'admin_id' => env('GUMBO_ADMIN_ID', 1),

    'payments' => [
        /**
         * Payment verification rates, all values are in milliseconds.
         */
        'verify' => [
            'refresh_rate' => 500,
            'timeout' => 1000,
        ],
    ],

    'tickets' => [
        'expiration' => [
            'anonymous' => 'PT15M',
            'authenticated' => 'PT1H',
        ],
    ],

    // Google config
    'google' => [
        // Allowed domains
        'domains' => [
            'gumbo-millennium.nl',
            'activiteiten.gumbo-millennium.nl',
            'gumbo.nu',
        ],
    ],

    // Redirect domanis
    'redirect-domains' => [
        // Bind gumbo.nu
        'gumbo.nu',

        // And bind to <redirect>.<your-domain-name> for testing
        sprintf('redirect.%s', $appHost),
    ],

    // Payment settings
    'payments' => [
        'default' => \App\Services\Payments\MolliePaymentService::getName(),
        'providers' => [
            \App\Services\Payments\MolliePaymentService::class,
        ],
    ],

    // Page groups
    'page-groups' => [
        'commissies' => 'Commissies',
        'disputen' => 'Disputen',
        'projectgroepen' => 'Projectgroepen',
    ],

    // Guzzle config
    'guzzle-config' => [
        // Identify ourselves when making requests
        'headers' => [
            'From' => 'bestuur@gumbo-millennium.nl',
            'User-Agent' => sprintf(
                'gumbo-millennium.nl/1.0 (incompatible; curl/%s; php/%s; https://www.gumbo-millennium.nl);',
                curl_version()['version'],
                PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            ),
        ],

        // Be snappy with declining the connection
        'connect_timeout' => 0.50,

        // Don't throw exceptions on response codes ≥ 400.
        'http_errors' => false,
    ],

    // News categories
    'news-categories' => [
        'Nieuws',
        'Aankondiging',
        'Persbericht',
        'Advertorial',
        'Vacature',
    ],

    // Quote e-mail address
    'quote-to' => env('GUMBO_QUOTE_EMAIL'),

    // Medial form fields
    'medical-titles' => [
        'allergieën',
        'allergies',
        'huisarts',
        'dokter',
    ],

    // Shop settings
    'shop' => [
        'order-limit' => (int) env('GUMBO_SHOP_MAX_QUANTITY', 5),

        // Seeded from resources/yaml/shop-features.yaml
        'features' => [],
    ],

    'mail-recipients' => [
        'board' => [
            [
                'name' => 'Bestuur Gumbo Millennium',
                'email' => 'bestuur@gumbo-millennium.nl',
            ],
        ],
    ],

    'fallbacks' => [
        'address' => [
            'line1' => 'Campus 2-6',
            'line2' => 't.a.v Gumbo Millennium',
            'postal_code' => '8017 CA',
            'city' => 'Zwolle',
            'country' => 'NL',
        ],
    ],

    /**
     * Activity features, seeded from Yaml file in resources.
     */
    'activity-features' => [
        // Don't add content here.
    ],

    /**
     * Data Retention settings.
     */
    'retention' => [
        'data-exports' => 'P30D',
        'enrollment-data' => 'P6M',
        'wallet-nonces' => 'PT6H',
    ],

    /**
     * Presets for Glide images.
     */
    'image-presets' => [
        'social' => [
            'width' => 1200,
            'height' => 630,
            'fit' => Image::FIT_CROP,
        ],
        'banner' => [
            'width' => 768,
            'height' => 256,
            'fit' => Image::FIT_CROP,
        ],
        'nova-thumbnail' => [
            'dpr' => 2,
            'width' => 32,
            'height' => 32,
            'fit' => Image::FIT_CROP,
        ],
        'nova-preview' => [
            'width' => 320,
            'height' => 320 / 3 * 4,
            'fit' => Image::FIT_CONTAIN,
        ],
        'tile' => [
            'width' => 607,
            'height' => 400,
            'fit' => Image::FIT_CROP,
        ],
    ],

    /**
     * Glide settings.
     */
    'glide' => [
        'source-disk' => env('GLIDE_SOURCE_DISK', 'public'),
        'source-path' => env('GLIDE_SOURCE_PATH', null),
        'cache-disk' => env('GLIDE_DISK', 'local'),
        'cache-path' => env('GLIDE_PATH', '.glide/image-cache'),
    ],

    /**
     * Image settings.
     */
    'images' => [
        'disk' => env('GUMBO_IMAGE_DISK', 'public'),
        'path' => env('GUMBO_IMAGE_PATH', 'images'),
    ],

    /**
     * Gallery settings.
     */
    'gallery' => [
        'max_photo_size' => env('GUMBO_GALLERY_MAX_PHOTO_SIZE', 8 * 1024 * 1024),
        'filepond' => [
            'disk' => env('GUMBO_IMAGE_DISK', 'public'),
            'path' => 'filepond/images/gallery',
        ],

        'exif' => [
            'database_path' => 'gumbo/gallery/exif-model-map.json',
        ],
    ],

    /**
     * Backup settings.
     */
    'backups' => [
        /**
         * Flag to indicate if backups will run.
         */
        'enabled' => env('BACKUP_ENABLED', false),

        /**
         * Number of days to keep incremental backups.
         */
        'incremental_preservation_days' => 16,

        /**
         * Number of days to keep full backups.
         */
        'full_preservation_days' => 30 * 6,

        /**
         * Location to store backups, should not overlap with other backup folders.
         */
        'storage_path' => Str::finish(env('BACKUP_LOCATION', 'backups/untagged'), '/'),

        /**
         * Location disk to use for backups.
         */
        'storage_disk' => env('BACKUP_DISK', 'glacier'),
    ],

    /**
     * Feature flags.
     */
    'features' => [
        /**
         * Enable barcode display on tickets.
         */
        'barcodes' => env('FEATURE_BARCODES', false),
    ],
];
