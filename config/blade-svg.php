<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SVG Path
    |--------------------------------------------------------------------------
    |
    | This value is the path to the directory of individual SVG files. This
    | path is then resolved internally. Please ensure that this value is
    | set relative to the root directory and not the public directory.
    |
    */

    'svg_path' => 'storage/app/font-awesome',

    /*
    |--------------------------------------------------------------------------
    | Inline Rendering
    |--------------------------------------------------------------------------
    |
    | This value will determine whether or not the SVG should be rendered inline
    | or if it should reference a spritesheet through a <use> element.
    |
    | Default: true
    |
    */

    'inline' => true,

    /*
    |--------------------------------------------------------------------------
    | Optional Class
    |--------------------------------------------------------------------------
    |
    | If you would like to have CSS classes applied to your SVGs, you must
    | specify them here. Much like how you would define multiple classes
    | in an HTML attribute, you may separate each class using a space.
    |
    | Default: ''
    |
    */

    'class' => 'icon',
];
