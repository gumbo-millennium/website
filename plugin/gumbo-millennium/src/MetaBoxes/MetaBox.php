<?php
declare(strict_types=1);

namespace Gumbo\Plugin\MetaBoxes;

use Gumbo\Plugin\Fields\Field;

/**
 * A meta box. Fields are added by subclasses
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class MetaBox
{
    /**
     * List of Fields to use when rendering the form
     *
     * @var array
     */
    protected $fields;

    /**
     * Name of the meta box, used internally
     *
     * @var string
     */
    protected $name = null;

    /**
     * Title of the meta box
     *
     * @var string
     */
    protected $title = null;

    /**
     * Post type to display the meta box at
     *
     * @var string
     */
    protected $postType = null;

    /**
     * Context to display the meta box on. One of 'normal', 'side' and 'advanced'.
     *
     * @var string|null
     */
    protected $context = null;

    /**
     * Createsa a new MetaBox, for the given post type.
     *
     * @param string $postType
     */
    public function __construct(string $postType)
    {
        // Register fields
        $this->postType = $postType;

        // Get local fields
        $this->fields = $this->registerFields();

        // Ensure there's a name to be given
        if (empty($this->name)) {
            $this->name = sanitize_title_with_dashes(get_called_class(), null, 'save');
        }
    }

    /**
     * Registers all hooks for this meta box.
     *
     * @return void
     */
    public function hook() : void
    {
        // Register hooks, with the protected methods
        add_action("add_meta_boxes_{$this->postType}", \Closure::fromCallable([$this, 'register']));
        add_action("save_post_{$this->postType}", \Closure::fromCallable([$this, 'store']), 10, 2);
    }

    /**
     * Returns the WordPress nonce field name
     *
     * @return string
     */
    final protected function getNonceName() : string
    {
        return "wp_{$this->name}_nonce";
    }

    /**
     * Returns the WordPress nonce action
     *
     * @return string
     */
    final protected function getNonceAction() : string
    {
        return "wp_{$this->name}_nonce_act";
    }

    /**
     * Registers the meta box with WordPress
     *
     * @return void
     */
    protected function register() : void
    {
        add_meta_box(
            "{$this->name}_meta",
            $this->title,
            \Closure::fromCallable([$this, 'render']),
            $this->postType,
            $this->context ?? 'advanced'
        );
    }

    /**
     * Actually renders the meta box, including the security nonce
     *
     * @param \WP_Post $post
     * @return void
     */
    public function render(\WP_Post $post) : void
    {
        // Print security nonce
        wp_nonce_field($this->getNonceAction(), $this->getNonceName());

        // Open table
        echo '<table class="form-table">';

        // Check if authorized
        $authorized = $this->isAuthorized();

        // Print each field
        foreach ($this->fields as $field) {
            if ($field instanceof Field) {
                $field->render($post, $authorized);
            }
        }

        // Close table
        echo '</table>';
    }

    /**
     * Saves the custom fields
     *
     * @param int $postId
     * @param \WP_Post $post
     * @return void
     */
    protected function store(int $postId, \WP_Post $post) : void
    {
        // Nonce validation
        $nonceValue = filter_input(INPUT_POST, $this->getNonceName());
        if (!wp_verify_nonce($nonceValue, $this->getNonceAction())) {
            return;
        }

        // User validation
        if (!$this->isAuthorized()) {
            return;
        }

        // Check if post is not a temporary item
        if (wp_is_post_autosave($post) || wp_is_post_revision($post)) {
            return;
        }

        // Save each field
        foreach ($this->fields as $field) {
            if ($field instanceof Field) {
                $field->store($post);
            }
        }
    }

    /**
     * Is the user allowed to use this meta box?
     *
     * @return bool
     */
    protected function isAuthorized() : bool
    {
        return true;
    }

    /**
     * Fields to add to the meta box
     *
     * @return array
     */
    abstract protected function registerFields() : array;
}
