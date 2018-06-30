<?php
declare(strict_types=1);

namespace Gumbo\Plugin\Fields;

/**
 * A field which has 'help' text
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
abstract class HelpField extends Field
{
    protected $help;

    /**
     * Creates a new field with the given name, label and help text
     *
     * @param string $name
     * @param string $label
     * @param string $help
     */
    public function __construct(string $name, string $label, string $help = null)
    {
        parent::__construct($name, $label);

        $help = $help ? trim($help) : null;
        $this->help = !empty($help) ? $help : null;
    }

    protected function hasHelp() : bool
    {
        return $this->help !== null;
    }

    /**
     * Returns the aria-describedby field for the HTML input
     *
     * @return string
     */
    protected function getHelpAria() : string
    {
        return sprintf(
            ' aria-describedby="%s_description"',
            $this->name
        );
    }

    /**
     * Returns the help HTML
     *
     * @return string
     */
    protected function getHelpHtml() : string
    {
        return sprintf(
            '<p class="description" id="%s_description">%s</p>',
            $this->name,
            esc_attr($this->help)
        );
    }
}
