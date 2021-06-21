<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Mail auto embed
    |--------------------------------------------------------------------------
    |
    | If true, images will be automatically embedded.
    | If false, only images with the 'data-auto-embed' attribute will be embedded
    |
    */

    'enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Mail embed method
    |--------------------------------------------------------------------------
    |
    | Supported: "attachment", "base64"
    |
    */

    'method' => 'attachment',
];
