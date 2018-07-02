<?php
declare(strict_types=1);

namespace Gumbo\Plugin\Fields;

/**
 * A text field, for extra text-based metadata
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class TextField extends HelpField
{
    /**
     * {@inheritDoc}
     */
    protected function printDisplay($value) : void
    {
        $html = <<<'HTML'
<tr>
    <th>
        <label for="%1$s" class="%1$s_label">%2$s</label>
    </th>
    <td>
        <p id="%1$s">%3$s</p>
    </td>
</tr>
HTML;

        printf(
            $html,
            $this->name,
            $this->label,
            empty($value) ? '<em>n/a</em>' : esc_attr($value)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function printField($value) : void
    {
        $html = <<<'HTML'
<tr>
    <th><label for="%1$s" class="%1$s_label">%2$s</label></th>
    <td>
        <input type="text" id="%1$s" name="%1$s" class="regular_text" placeholder="%3%s" value="%4$s" %5$s>
        %6$s
    </td>
</tr>
HTML;

        printf(
            $html,
            $this->name,
            $this->label,
            esc_attr($this->label),
            $value === null ? '' : esc_attr($value),
            $this->hasHelp() ? $this->getHelpAria() : null,
            $this->hasHelp() ? $this->getHelpHtml() : null
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function filterData($value)
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        } else {
            return $value;
        }
    }
}
