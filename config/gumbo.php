<?php

declare(strict_types=1);

return [
    // Application status, based on URL
    'beta' => env('GUMBO_BETA', env('APP_URL', 'http://localhost') !== 'https://www.gumbo-millennium.nl'),

    // Cost of a single payment transaction
    'transfer-fee' => 40,

    // Google config
    'google' => [
        // Allowed domains
        'domains' => [
            'gumbo-millennium.nl',
            'activiteiten.gumbo-millennium.nl',
            'gumbo.nu',
        ],
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
        'max-quantity' => (int) env('GUMBO_SHOP_MAX_QUANTITY', 5),
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
];
