<?php

declare(strict_types=1);

use App\Fluent\Image;

return [
    // Application status, based on URL
    'beta' => env('GUMBO_BETA', env('APP_URL', 'http://localhost') !== 'https://www.gumbo-millennium.nl'),

    // Cost of a single payment transaction
    'transfer-fee' => 40,

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
        sprintf('redirect.%s', parse_url(env('APP_URL'), PHP_URL_HOST)),
    ],

    // Payment settings
    'payments' => [
        'default' => \App\Services\Payments\MolliePaymentService::getName(),
        'providers' => [
            \App\Services\Payments\MolliePaymentService::class,
        ],
    ],

    // Lustrum mini-site
    'lustrum-domains' => [
        'gumbolustrum.nl',
        'langzalgumboleven.nl',

        // And bind to <redirect>.<your-domain-name> for testing
        sprintf('lustrum.%s', parse_url(env('APP_URL'), PHP_URL_HOST)),
    ],

    // Page groups
    'page-groups' => [
        'commissies' => 'Commissies',
        'disputen' => 'Disputen',
        'projectgroepen' => 'Projectgroepen',
        'coronavirus' => 'Gumbo en de Coronacrisis',
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

    // Activity features, seeded from Yaml file in resources
    'activity-features' => [
        // Don't add content here.
    ],

    // Data Exports
    'export-expire-days' => 30,

    // Preferred banks
    'preferred-banks' => [
        'ideal_RABONL2U',
        'ideal_ABNANL2A',
        'ideal_INGBNL2A',
    ],

    // Presets
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
            'dpr' => 2,
            'width' => 700,
            'height' => 400,
            'fit' => Image::FIT_CROP,
        ],
    ],

    // Glide settings
    'glide' => [
        'source-disk' => env('GLIDE_SOURCE_DISK', 'public'),
        'source-path' => env('GLIDE_SOURCE_PATH', null),
        'cache-disk' => env('GLIDE_DISK', 'local'),
        'cache-path' => env('GLIDE_PATH', '.glide/image-cache'),
    ],

    // Image settings
    'images' => [
        'disk' => env('GUMBO_IMAGE_DISK', 'public'),
        'path' => env('GUMBO_IMAGE_PATH', 'images'),
    ],
];
