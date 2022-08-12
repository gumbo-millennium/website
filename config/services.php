<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services.
    | This file provides a sane default location for this type of information,
    | allowing packages to have a conventional place to find your various
    | credentials.
    |
    */

    // Conscribo API
    'conscribo' => [
        'account' => env('CONSCRIBO_ACCOUNT_NAME'),
        'username' => env('CONSCRIBO_USERNAME'),
        'password' => env('CONSCRIBO_PASSPHRASE'),
        'resources' => [
            'user' => env('CONSCRIBO_RESOURCE_USERS', 'persoon'),
            'role' => env('CONSCRIBO_RESOURCE_ROLE', 'commissie'),
        ],
    ],

    // iZettle API
    'izettle' => [
        'client-id' => env('IZETTLE_CLIENT_ID'),
        'client-assertion' => env('IZETTLE_CLIENT_ASSERTION'),
    ],

    // Google APIs
    'google' => [
        // Key file
        'key-file' => env('GOOGLE_AUTH_FILE', storage_path('auth/google.json')),

        // Set user who we use to sign in as
        'subject' => $googleSubject = env('GOOGLE_AUTH_USER', 'domain-admin@gumbo-millennium.nl'),

        // Make sure our Service Account logged in using the auth file is granted
        // domain-wide authority. For more information, read this:
        // https://github.com/googleapis/google-api-php-client/blob/master/docs/oauth-server.md#delegating-domain-wide-authority-to-the-service-account
        'scopes' => [
            \Google\Service\Directory::ADMIN_DIRECTORY_GROUP,
            \Google\Service\Groupssettings::APPS_GROUPS_SETTINGS,
        ],

        // Allowed domains
        'domains' => [
            'gumbo.nu',
            'gumbo-millennium.nl',
            'activiteiten.gumbo-millennium.nl',
            'organen.gumbo-millennium.nl',
        ],

        // Google Wallet configuration
        'wallet' => [
            // JSON key
            'key_file' => env('GOOGLE_WALLET_AUTH_FILE', env('GOOGLE_AUTH_FILE', storage_path('auth/google-wallet.json'))),

            // Subject
            'subject' => env('GOOGLE_WALLET_SUBJECT', $googleSubject),

            // Google Wallet Issuer ID
            'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID', null),
        ],
    ],

    // Tenor gif search
    'tenor' => [
        'api-key' => env('TENOR_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature flags
    |--------------------------------------------------------------------------
    |
    | These flags indicate if a certain feature is available for this platform.
    | These features might be disabled by choice or if a certain dependency
    | is not available (which is the case with Laravel Nova)
     */
    'features' => [
        // Only enable Laravel Nova if installed and not disabled by the user
        'enable-nova' => env('FEATURE_DISABLE_NOVA', false) !== true,
    ],
];
