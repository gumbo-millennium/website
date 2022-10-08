<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventClass;
use App\Models\GoogleWallet\EventObject;

trait HandlesActivityTypes
{
    abstract protected function buildEventClassForActivity(Activity $activity): EventClass;

    abstract protected function buildEventObjectForEnrollment(Enrollment $enrollment): EventObject;

    abstract public function writeEventClass(EventClass $eventClass): EventClass;

    abstract public function writeEventObject(EventObject $eventObject): EventObject;

    /**
     * Writes a given EventClass to the Google Wallet API.
     */
    public function writeEventClassForActivity(Activity $activity): EventClass
    {
        throw_unless(
            $activity->exists,
            LogicException::class,
            'The activity must exist before it can be created in Google Wallet.',
        );

        // Resovle the activity to an event class
        $eventClass = $this->buildEventClassForActivity($activity);

        // Write the event class to the Google Wallet API
        $this->writeEventClass($eventClass);

        // Return the event class.
        return $eventClass;
    }
}
