<?php

/**
 * Gumbo Config
 */

return [
    // Application status, based on URL
    'beta' => env('GUMBO_BETA', env('APP_URL', 'http://localhost') !== 'https://www.gumbo-millennium.nl'),

    // Conscribo API
    'conscribo' => [
        'account-name' => env('CONSCRIBO_ACCOUNT_NAME'),
        'username' => env('CONSCRIBO_USERNAME'),
        'passphrase' => env('CONSCRIBO_PASSPHRASE'),
    ],

    // Guzzle config
    'guzzle-config' => [
        // Identify ourselves when making requests
        'headers' => [
            'From' => 'bestuur@gumbo-millennium.nl',
            'User-Agent' => sprintf(
                'gumbo-millennium.nl/1.0 (incompatible; curl/%s; php/%s; https://www.gumbo-millennium.nl);',
                curl_version()['version'],
                PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION
            )
        ],

        // Be snappy with declining the connection
        'connect_timeout' => 0.50,

        // Don't throw exceptions on response codes ≥ 400.
        'http_errors' => false
    ]
];
