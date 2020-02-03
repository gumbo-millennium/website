<?php

declare(strict_types=1);

/**
 * Gumbo Config
 */

return [
    // Application status, based on URL
    'beta' => env('GUMBO_BETA', env('APP_URL', 'http://localhost') !== 'https://www.gumbo-millennium.nl'),

    // Cost of a single payment transaction via Stripe, in cents!
    'transfer-fee' => 35,

    // Google config
    'google' => [
        // Allowed domains
        'domains' => [
            'gumbo-millennium.nl',
            'activiteiten.gumbo-millennium.nl',
            'gumbo.nu'
        ],
        'auth' => [
            // Key file
            'key-file' => env('GOOGLE_AUTH_FILE', storage_path('auth/google.json')),

            // Set user who we use to sign in as
            'subject' => env('GOOGLE_AUTH_USER', 'domain-admin@gumbo-millennium.nl'),

            // Make sure our Service Account logged in using the auth file is granted
            // domain-wide authority. For more information, read this:
            // https://github.com/googleapis/google-api-php-client/blob/master/docs/oauth-server.md#delegating-domain-wide-authority-to-the-service-account
            'scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group'
            ]
        ]
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
