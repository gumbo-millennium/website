<?php

declare(strict_types=1);

namespace App\Services\Google\Traits;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\GoogleWallet\EventClass;
use App\Models\GoogleWallet\EventObject;
use App\Models\User;
use RuntimeException;

trait HandlesActivityTypes
{
    abstract protected function buildEventClassForActivity(Activity $activity): EventClass;

    abstract protected function buildEventObjectForEnrollment(Enrollment $enrollment): EventObject;

    abstract public function writeEventClass(EventClass $eventClass): EventClass;

    abstract public function writeEventObject(EventObject $eventObject): EventObject;

    abstract public function getImportUrl(EventObject $eventObject): string;

    /**
     * Writes a given Activity to the Google Wallet API.
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

    /**
     * Writes a given Enrollment to the Google Wallet API.
     */
    public function writeEventObjectForEnrollment(Enrollment $enrollment): EventObject
    {
        throw_unless(
            $enrollment->exists,
            LogicException::class,
            'The enrollment must exist before it can be created in Google Wallet.',
        );

        // Resovle the activity to an event class
        $eventObject = $this->buildEventObjectForEnrollment($enrollment);

        // Write the event class to the Google Wallet API
        $this->writeEventObject($eventObject);

        // Return the event class.
        return $eventObject;
    }

    public function getImportUrlForEnrollment(User $user, Enrollment $enrollment): ?string
    {
        // See if a token is available
        $eventObject = EventObject::forSubject($enrollment)->first();
        if (! $eventObject) {
            return null;
        }

        // Ensure the user claiming is the user owning
        if (! $user->is($enrollment->user)) {
            return null;
        }

        // Ensure the state is valid and the token isn't consumed
        if (! $enrollment->is_stable || $enrollment->consumed()) {
            return null;
        }

        try {
            return $this->getImportUrl($eventObject);
        } catch (RuntimeException) {
            return null;
        }
    }
}
