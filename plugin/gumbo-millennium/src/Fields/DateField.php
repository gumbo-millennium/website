<?php
declare(strict_types=1);

namespace Gumbo\Plugin\Fields;

/**
 * A date field, to pick dates
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class DateField extends HelpField
{
    /**
     * {@inheritDoc}
     */
    protected function printHtml($value) : void
    {
        $html = <<<'HTML'
<tr>
    <th>
        <label for="%1$s" class="%1$s_label">%2$s</label>
    </th>
    <td>
        <input
            type="text"
            id="%1$s"
            name="%1$s"
            class="%1$s_field"
            placeholder="dd-mm-yyyy"
            value="%4$s"
            data-cleave="date"
            %5$s>
        %6$s
    </td>
</tr>
HTML;

        printf(
            $html,
            $this->name,
            $this->label,
            esc_attr($this->label),
            esc_attr($value),
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

        // Empty values
        if (empty($value)) {
            return null;
        }

        // Non-date values
        if (!preg_match('/^[0-3]\d-[0-1]\d-20\d\d$/', $value)) {
            return null;
        }

        // Convert to date object, then back to timestamp. This takes out any weird date constructs
        return \DateTimeImmutable::createFromFormat('d-m-Y', $value)->format('d-m-Y');
    }
}
