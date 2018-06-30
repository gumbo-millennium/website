<?php
declare(strict_types=1);

namespace Gumbo\Plugin\Fields;

/**
 * A time field, to choose times
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class TimeField extends HelpField
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
            placeholder="hh:mm"
            value="%4$s"
            data-cleave="time"
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
        if (!preg_match('/^(?:[0-1]\d|2[0-3]):[0-5]\d$/', $value)) {
            return null;
        }

        // Convert to date object, then back to timestamp. This takes out any weird date constructs
        return \DateTimeImmutable::createFromFormat('h:m', $value)->format('h:m');
    }
}
