<?php
declare(strict_types=1);

namespace Gumbo\Plugin\MetaBoxes;

use Gumbo\Plugin\Fields\DateField;
use Gumbo\Plugin\Fields\TimeField;

/**
 * A meta box for activities, which include dates, prices and such
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class ActivityBox extends MetaBox
{
    /**
     * Name of the meta box, used internally
     *
     * @var string
     */
    protected $name = 'activity';

    /**
     * Title of the meta box
     *
     * @var string
     */
    protected $title = "Activiteit instellingen";

    /**
     * Context to display the meta box in, one of 'normal', 'side' and 'advanced'.
     *
     * Use null to use default ('advanced')
     *
     * @var string
     */
    protected $context = 'normal';

    /**
     * Is the user allowed to use this meta box?
     *
     * @return bool
     */
    protected function isAuthorized() : bool
    {
        return current_user_can('edit_activity');
    }

    /**
     * {@inheritDoc}
     */
    protected function registerFields() : array
    {
        $helpTime = sprintf(
            'Tijdzone: UTC%s',
            (new \DateTime('now', new \DateTimeZone('Europe/Amsterdam')))->format('P')
        );

        return [
            new DateField('start_date', 'Start datum'),
            new TimeField('start_time', 'Start tijd', $helpTime),
            new DateField('end_date', 'Eind datum'),
            new TimeField('end_time', 'Eind tijd', $helpTime)
        ];
    }
}
