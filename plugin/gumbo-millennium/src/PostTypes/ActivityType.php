<?php
declare(strict_types=1);

namespace Gumbo\Plugin\PostTypes;

use PHPUnit\Framework\Constraint\Callback;
use Gumbo\Plugin\MetaBoxes\ActivityBox;

/**
* Registers the activity type, which is used to schedule events.
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/
class ActivityType extends PostType
{
    /**
     * {@inheritDoc}
     */
    protected function getName() : string
    {
        return 'gumbo-activity';
    }

    /**
     * {@inheritDoc}
     */
    protected function getProperties() : array
    {
        return [
            'labels' => [
                'name' => __('Activities'),
                'singular_name' => __('Activity')
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'activiteiten'],
            'supports' => [
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'custom-fields',
                'revisions'
            ]
        ];
    }

    protected function getMetaFields() : array
    {
        return [
            ActivityBox::class
        ];
    }

    private function renderDates() : void
    {
        echo <<<HTML
<p>WORDS</p>
HTML;
    }
}
