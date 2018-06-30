<?php
declare (strict_types = 1);

namespace Gumbo\Plugin\PostTypes;

use Gumbo\Plugin\MetaBoxes\MetaBox;

/**
 * Registers a single post type. Auto-filters all capabilities of a type.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class PostType
{
    const POST_FEATURES = [
        'title',
        'editor',
        'author',
        'thumbnail',
        'excerpt',
        'trackbacks',
        'custom-fields',
        'comments',
        'revisions',
        'page-attributes',
        'post-formats'
    ];

    /**
     * Returns the internal name of the post type
     *
     * @return string
     */
    abstract protected function getName() : string;

    /**
     * Returns the configuration properties of the type.
     *
     * @return array
     */
    abstract protected function getProperties() : array;

    /**
     * Returns a list of meta box classes, which should be registered alongside this post type.
     *
     * @return array Fully qualified class names of the meta boxes
     */
    protected function getMetaFields() : array
    {
        // By default, no meta fields are added
        return [];
    }

    /**
     * Registers the post type
     *
     * @return void
     */
    public function registerType()
    {
        // Get properties
        $name = $this->getName();
        $properties = $this->getProperties();

        // Register the post type
        register_post_type($name, $this->getProperties());

        // Register meta fields
        foreach ($this->getMetaFields() as $metaField) {
            if (is_a($metaField, MetaBox::class, true)) {
                $field = new $metaField($name);
                $field->hook();
            } else {
                throw new \LogicException(sprintf(
                    'Expected [%s] to be a subclass of [%s]. It\'s not.',
                    $metaField,
                    MetaBox::class
                ));
            }
        }
    }
}
