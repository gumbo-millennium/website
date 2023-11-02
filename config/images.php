<?php

declare(strict_types=1);

return [
    /**
     * Source settings.
     *
     * The disk to get the source images from. A path may be specified
     * to refuse processing of images that are not in the specified
     * directory.
     */
    'source' => [
        'disk' => env('IMAGES_SOURCE_DISK', 'public'),
        'path' => env('IMAGES_SOURCE_PATH', 'images'),
    ],

    /**
     * Storage Settings.
     *
     * Where to store the images and how to serve them.
     */
    'storage' => [
        /**
         * Which disk and path to use.
         */
        'disk' => env('IMAGES_STORAGE_DISK', 'public'),
        'path' => 'model-images/generated',

        /**
         * Should the URLs be signed?
         */
        'use_temporary_urls' => (bool) env('IMAGES_STORAGE_TEMP_URL', false),
    ],

    /**
     * Image Sizes.
     *
     * Image sizes should have a height, width and a flag if they should be cropped.
     */
    'sizes' => [
        'social' => [
            'width' => 1200,
            'height' => 630,
            'crop' => true,
        ],
        'banner' => [
            'width' => 768,
            'height' => 256,
            'crop' => true,
        ],
        'tile' => [
            'width' => 607,
            'height' => 400,
            'crop' => true,
        ],
        'nova-thumbnail' => [
            'dpr' => 2,
            'width' => 32,
            'height' => 32,
            'crop' => true,
        ],
        'nova-preview' => [
            'width' => 320,
            'height' => 320 / 3 * 4,
            'crop' => false,
        ],
    ],
];
