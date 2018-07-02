<?php
declare(strict_types=1);

namespace Gumbo\Plugin\Fields;

/**
 * A dynamic field, used to render extra fields in meta boxes
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class Field
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * Creates a new field with the given name and label
     *
     * @param string $name
     * @param string $label
     */
    public function __construct(string $name, string $label)
    {
        if (!preg_match('/^[a-z][a-z0-9_\-]+$/', $name)) {
            throw new \LogicException(sprintf(
                'Name "%s" is invalid, can only contain a-z, 0-9, dashes and underscores.',
                $name
            ));
        }

        $this->name = $name;
        $this->label = $label;
    }

    /**
     * Actually renders the form, by retrieving the corresponding data from the database.
     *
     * @param \WP_Post $post
     * @return void
     */
    public function render(\WP_Post $post, bool $authorized) : void
    {
        $value = get_post_meta($post->ID, $this->name, true);

        if ($value === '') {
            $value = $this->getDefaultValue();
        }

        if ($authorized) {
            $this->printField($value);
        } else {
            $this->printDisplay($value);
        }
    }

    /**
     * Stores the single field type for the given post
     *
     * @param int $id
     * @return void
     */
    public function store(\WP_Post $post) : void
    {
        // Get value from form submission
        $value = filter_has_var(INPUT_POST, $this->name) ? filter_input(INPUT_POST, $this->name) : null;

        // Validate value if it's not empty
        $value = ($value === null || $value === '') ? null : $this->filterData($value);

        // Update the meta field in the database.
        update_post_meta($post->ID, $this->name, $value);
    }

    /**
     * Prints the display of the value. Called instead of printField when the user
     * is not allowed to change the value.
     *
     * @param mixed $value
     * @return void
     */
    protected function printDisplay($value) : void
    {
        $this->printField($value);
    }

    /**
     * Renders HTML for the form
     *
     * @param mixed $value
     * @return void
     */
    abstract protected function printField($value) : void;

    /**
     * Sanitizes data for the field
     *
     * @param mixed $value  "Dirty" value
     * @return mixed        Clean value
     */
    abstract protected function filterData($value);

    /**
     * Returns the default value for the item. Defaults to a null value.
     *
     * @return mixed
     */
    protected function getDefaultValue()
    {
        return null;
    }
}
