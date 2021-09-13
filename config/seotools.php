<?php

declare(strict_types=1);

return [
    'meta' => [
        // The default configurations to be used by the meta generator.
        'defaults' => [
            'title' => 'Gumbo Millennium', // set false to total remove
            'titleBefore' => false, // Put defaults.title before page title, like 'It's Over 9000! - Dashboard'
            'description' => 'Gumbo Millennium staat bekend als de gezelligste studentenvereniging van Zwolle! Bij ons vind je de leukste (studenten-)activiteiten!',
            'separator' => ' - ',
            'keywords' => [],
            'canonical' => false,
            'robots' => env('GUMBO_BETA') ? 'noindex,nofollow' : 'all',
        ],

        // Webmaster tags are always added.
        'webmaster_tags' => [
            'google' => null,
            'bing' => null,
            'alexa' => null,
            'pinterest' => null,
            'yandex' => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        // The default configurations to be used by the opengraph generator.
        'defaults' => [
            'title' => 'Gumbo Millennium',
            'description' => 'Welkom bij de gezelligste studentenvereniging van Zwolle',
            'url' => false,
            'type' => false,
            'site_name' => 'Gumbo Millennium.nl',
            'images' => [],
        ],
    ],
    'twitter' => [
        // We don't use Twitter
    ],
    'json-ld' => [
        // The default configurations to be used by the json-ld generator.
        'defaults' => [
            'title' => false,
            'description' => false,
            'url' => false, // Set null for using Url::current(), set false to total remove
            'type' => 'WebPage',
            'images' => [],
        ],
    ],
];
