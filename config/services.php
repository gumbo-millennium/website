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

    // Mollie API
    'mollie' => [
        'test-mode' => env('MOLLIE_TEST_MODE', false),
        'api-key' => env('MOLLIE_API_KEY')
    ],

    // Payments in general
    'payments' => [
        'default-provider' => env('PAYMENT_PROVIDER', 'mollie'),
        'providers' => [
            'mollie' => App\Services\Payments\MolliePaymentService::class,
        ]
    ],

    // Conscribo API
    'conscribo' => [
        'account-name' => env('CONSCRIBO_ACCOUNT_NAME'),
        'username' => env('CONSCRIBO_USERNAME'),
        'passphrase' => env('CONSCRIBO_PASSPHRASE'),
        'resources' => [
            'user' => env('CONSCRIBO_RESOURCE_USERS', 'persoon'),
            'role' => env('CONSCRIBO_RESOURCE_ROLE', 'commissie')
        ]
    ],

    // Google API
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
            \Google_Service_Groupssettings::APPS_GROUPS_SETTINGS
        ],

        // Allowed domains
        'domains' => [
            'gumbo-millennium.nl',
            'activiteiten.gumbo-millennium.nl'
        ],
    ],

    // Glide (images)
    'glide' => [
        'cache-path' => null,
        'presets' => [
            'social' => [
                'w' => 1200,
                'h' => 630,
                'fit' => 'crop'
            ],
            'social-webp' => [
                'w' => 1200,
                'h' => 630,
                'fit' => 'crop',
                'fm' => 'webp'
            ]
        ]
    ]
];
