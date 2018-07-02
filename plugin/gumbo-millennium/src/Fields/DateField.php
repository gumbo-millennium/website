<?php
declare(strict_types=1);

namespace Gumbo\Plugin\Fields;

/**
 * A date field, to pick dates
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class DateField extends TextField
{
    /**
     * {@inheritDoc}
     */
    protected function printField($value) : void
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
            value="%3$s"
            data-cleave="date"
            %4$s>
        %5$s
    </td>
</tr>
HTML;

        vprintf($html, [
            $this->name,
            $this->label,
            esc_attr($value),
            $this->hasHelp() ? $this->getHelpAria() : null,
            $this->hasHelp() ? $this->getHelpHtml() : null
        ]);
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
        if (!preg_match('/^[0-3]\d-[0-1]\d-\d{4}$/', $value)) {
            return null;
        }

        // Convert to date object, then back to timestamp. This takes out any weird date constructs
        return \DateTimeImmutable::createFromFormat('d-m-Y', $value)->format('d-m-Y');
    }
}
