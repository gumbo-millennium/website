<?php

declare(strict_types=1);

return [
    /**
     * If we should handle live or testing webhooks.
     */
    'test_mode' => env('STRIPE_TEST_MODE', true),

    // Stripe publishable key
    'public_key' => env('STRIPE_PUBLIC_KEY'),

    /**
     * Stripe private key.
     */
    'private_key' => env('STRIPE_PRIVATE_KEY'),
];
