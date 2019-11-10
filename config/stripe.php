<?php

return [
    /*
     * Stripe publishable key
     */
    'public_key' => env('STRIPE_PUBLIC_KEY'),

    /**
     * Stripe private key
     */
    'private_key' => env('STRIPE_PRIVATE_KEY'),
];
