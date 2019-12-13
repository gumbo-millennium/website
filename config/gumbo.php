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
    ]
];
