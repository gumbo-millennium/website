<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    // Stripe API
    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

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

    // Google APIs
    'google' => [
        // Key file
        'key-file' => env('GOOGLE_AUTH_FILE', storage_path('auth/google.json')),

        // Set user who we use to sign in as
        'subject' => env('GOOGLE_AUTH_USER', 'domain-admin@gumbo-millennium.nl'),

        // Make sure our Service Account logged in using the auth file is granted
        // domain-wide authority. For more information, read this:
        // https://github.com/googleapis/google-api-php-client/blob/master/docs/oauth-server.md#delegating-domain-wide-authority-to-the-service-account
        'scopes' => [
            \Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
            \Google_Service_Groupssettings::APPS_GROUPS_SETTINGS,
        ],

        // Allowed domains
        'domains' => [
            'gumbo.nu',
            'gumbo-millennium.nl',
            'activiteiten.gumbo-millennium.nl',
            'organen.gumbo-millennium.nl',
        ],
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
