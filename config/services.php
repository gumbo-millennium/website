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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    // Stripe API
    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
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

];
