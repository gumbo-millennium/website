<?php

declare(strict_types=1);

return [
    /**
     * The base URL of the Conscribo API. Usually you won't have to change this.
     */
    'base_url' => env('CONSCRIBO_HOST', 'https://secure.conscribo.nl'),

    /**
     * Account name.
     */
    'account' => env('CONSCRIBO_ACCOUNT_NAME'),

    /**
     * Account username and password.
     */
    'username' => env('CONSCRIBO_USERNAME'),
    'password' => env('CONSCRIBO_PASSPHRASE'),

    /**
     * Resources to retrieve.
     */
    'resources' => [
        'user' => env('CONSCRIBO_RESOURCE_USERS', 'persoon'),
        'role' => env('CONSCRIBO_RESOURCE_ROLE', 'commissie'),
    ],
];
