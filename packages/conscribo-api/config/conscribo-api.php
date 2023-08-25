<?php

declare(strict_types=1);

return [
    /**
     * Conscribo API configuration.
     */
    'conscribo' => [
        /**
         * Which account is to be used? All other parameters will act upon this account.
         */
        'account' => env('CONSCRIBO_ACCOUNT'),

        /**
         * The username and password to use for authentication.
         */
        'username' => env('CONSCRIBO_USERNAME'),
        'password' => env('CONSCRIBO_PASSWORD'),

        /**
         * The core entity mappings, should match the name of people and groups in the Conscribo Application.
         */
        'entities' => [
            'user' => env('CONSCRIBO_USERS_ENTITY', 'person'),
            'group' => env('CONSCRIBO_USERS_GROUP', 'group'),
        ],
    ],
];
